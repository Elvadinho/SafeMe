<?php

namespace Modules\Children\Services;

use App\Models\User;
use App\Services\FileService;
use App\Support\Geo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Children\Enums\DocumentType;
use Modules\Children\Enums\ReportStatus;
use Modules\Children\Enums\SightingStatus;
use Modules\Children\Models\MissingReport;
use Throwable;

/**
 * Business logic of missing child reports:
 * submit → admin review (approve / reject) → public alert → found / closed.
 */
class MissingReportService
{
    /** Fields only admins may see. */
    private const ADMIN_FIELDS = ['reporter_name', 'reporter_phone', 'rejection_reason'];

    public function __construct(private FileService $files) {}

    // ── Reporter side ──────────────────────────────────

    /** Saves a new report (status = pending) with its photo and proof documents. */
    public function submit(array $data): MissingReport
    {
        $savedPaths = []; // to clean up if something fails

        try {
            // 1. Files first, outside the DB transaction (uploads are slow,
            //    a transaction must stay short).
            $photoPath = $this->files->storePhoto($data['child_photo'], 'children/photos');
            $savedPaths[] = $photoPath;

            $documents = $this->storeDocuments($data['documents'] ?? [], $savedPaths);

            // 2. All rows or no rows.
            $report = DB::transaction(function () use ($data, $photoPath, $documents) {
                $fields = Arr::except($data, ['child_photo', 'documents', 'declaration']);
                $report = MissingReport::create([...$fields, 'child_photo_path' => $photoPath]);
                $report->documents()->createMany($documents);

                return $report;
            });
        } catch (Throwable $e) {
            // 3. Failed: delete the files we already saved, then show the error.
            $this->files->delete($savedPaths);
            throw $e;
        }

        // Phase 2: notify the admins by SMS / push here.
        Log::info('Missing report submitted', ['report' => $report->public_id]);

        return $report->load('documents');
    }

    /** The reporter proves who they are with the phone number used in the report. */
    public function ensureReporter(MissingReport $report, string $phone): void
    {
        // 404 (not 403): a stranger must not even learn that this report exists.
        // hash_equals compares in constant time (no timing attack).
        abort_unless(hash_equals($report->reporter_phone, $phone), 404);
    }

    /** Adds proof documents later (the parent did not have them at the start). */
    public function addDocuments(MissingReport $report, array $documents): MissingReport
    {
        abort_unless($report->status === ReportStatus::Pending, 409, 'Documents can only be added while the report is pending.');

        $savedPaths = [];

        try {
            $rows = $this->storeDocuments($documents, $savedPaths);
            $report->documents()->createMany($rows);
        } catch (Throwable $e) {
            $this->files->delete($savedPaths);
            throw $e;
        }

        return $report->load('documents');
    }

    /** What the reporter sees when following their case. */
    public function reporterView(MissingReport $report): array
    {
        $report->load(['documents', 'sightings' => fn ($query) => $query->latest('seen_at')]);

        return [
            'report'            => $report->withoutRelations(),
            'documents'         => $report->documents,
            'missing_documents' => $this->documentValues($report->missingDocumentTypes()),
            'rejection_reason'  => $report->status === ReportStatus::Rejected ? $report->rejection_reason : null,
            // The family sees every sighting at once, even unconfirmed ones.
            'sightings'         => $report->sightings,
        ];
    }

    // ── Public side ────────────────────────────────────

    /**
     * Approved alerts, newest first. With lat/lng, only alerts whose search
     * centre is within $radiusKm of that point (e.g. "alerts near me").
     */
    public function publicAlerts(?float $lat, ?float $lng, float $radiusKm = 50): array
    {
        $reports = MissingReport::visibleToPublic()
            ->with(['sightings' => fn ($query) => $query->where('status', SightingStatus::Confirmed)])
            ->latest('last_seen_at')
            ->limit(200)
            ->get();

        $alerts = [];

        foreach ($reports as $report) {
            $zone = $this->searchZone($report);
            $distance = ($lat !== null && $lng !== null)
                ? round(Geo::distanceKm($lat, $lng, $zone['lat'], $zone['lng']), 1)
                : null;

            if ($distance !== null && $distance > $radiusKm) {
                continue;
            }

            $alerts[] = ['alert' => $report->withoutRelations(), 'search_zone' => $zone, 'distance_km' => $distance];
        }

        return $alerts;
    }

    /** One public alert with its search zone and confirmed sightings. */
    public function publicAlert(MissingReport $report): array
    {
        abort_unless($report->status->isPublic(), 404);

        $report->load(['sightings' => fn ($query) => $query->where('status', SightingStatus::Confirmed)->latest('seen_at')]);

        return [
            'alert'       => $report->withoutRelations(),
            'search_zone' => $this->searchZone($report),
            'sightings'   => $report->sightings,
        ];
    }

    /**
     * Where to search, and how wide.
     * Centre = the latest CONFIRMED sighting, or the last-seen place.
     * Radius grows with the time since then: a child can be further away every hour.
     */
    public function searchZone(MissingReport $report): array
    {
        $latest = $report->sightings
            ->where('status', SightingStatus::Confirmed)
            ->sortByDesc('seen_at')
            ->first();

        $lat = (float) ($latest->lat ?? $report->last_seen_lat);
        $lng = (float) ($latest->lng ?? $report->last_seen_lng);
        $since = $latest->seen_at ?? $report->last_seen_at;

        $cfg = config('safeme.search_zone');
        $hours = max(0, $since->diffInMinutes(now()) / 60);
        $radius = min($cfg['start_km'] + $cfg['km_per_hour'] * $hours, $cfg['max_km']);

        return [
            'lat'       => $lat,
            'lng'       => $lng,
            'radius_km' => round($radius, 1),
            'since'     => $since->toIso8601String(),
        ];
    }

    // ── Admin side ─────────────────────────────────────

    public function adminList(?string $status): LengthAwarePaginator
    {
        $status = ReportStatus::tryFrom((string) $status) ?? ReportStatus::Pending;

        return MissingReport::query()
            ->where('status', $status)
            ->withCount(['documents', 'sightings'])
            ->oldest()
            ->paginate(20)
            ->through(fn (MissingReport $report) => $report->makeVisible(self::ADMIN_FIELDS));
    }

    public function adminView(MissingReport $report): array
    {
        $report->load(['documents', 'sightings' => fn ($query) => $query->latest('seen_at'), 'reviewer:id,name'])
            ->makeVisible(self::ADMIN_FIELDS);

        $report->sightings->each->makeVisible(['witness_phone']);

        return [
            'report'            => $report,
            'missing_documents' => $this->documentValues($report->missingDocumentTypes()),
            'search_zone'       => $this->searchZone($report),
        ];
    }

    public function approve(MissingReport $report, User $admin): MissingReport
    {
        return $this->changeStatus($report, ReportStatus::Approved, [
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);
    }

    public function reject(MissingReport $report, User $admin, string $reason): MissingReport
    {
        return $this->changeStatus($report, ReportStatus::Rejected, [
            'reviewed_by'      => $admin->id,
            'reviewed_at'      => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function markFound(MissingReport $report): MissingReport
    {
        return $this->changeStatus($report, ReportStatus::Found, ['resolved_at' => now()]);
    }

    public function close(MissingReport $report): MissingReport
    {
        return $this->changeStatus($report, ReportStatus::Closed, ['resolved_at' => now()]);
    }

    // ── Private helpers ────────────────────────────────

    /** Every status change goes through the rules of ReportStatus::canTransitionTo(). */
    private function changeStatus(MissingReport $report, ReportStatus $next, array $extra): MissingReport
    {
        abort_unless(
            $report->status->canTransitionTo($next),
            409,
            "A {$report->status->value} report cannot become {$next->value}."
        );

        // forceFill: status is not fillable on purpose, only this service changes it.
        $report->forceFill(['status' => $next, ...$extra])->save();

        Log::info('Missing report status changed', ['report' => $report->public_id, 'status' => $next->value]);

        return $report->makeVisible(self::ADMIN_FIELDS);
    }

    /**
     * Saves each document file and returns the rows to insert.
     * $savedPaths is filled as we go, so the caller can clean up on failure.
     */
    private function storeDocuments(array $documents, array &$savedPaths): array
    {
        $rows = [];

        foreach ($documents as $document) {
            $file = $document['file'];
            $path = $this->files->storeFile($file, 'children/documents');
            $savedPaths[] = $path;

            $rows[] = [
                'type'       => $document['type'],
                'path'       => $path,
                'mime_type'  => (string) $file->getMimeType(), // detected from the content
                'size_bytes' => (int) $file->getSize(),
            ];
        }

        return $rows;
    }

    /** @param list<DocumentType> $types */
    private function documentValues(array $types): array
    {
        return array_map(fn (DocumentType $type) => $type->value, $types);
    }
}
