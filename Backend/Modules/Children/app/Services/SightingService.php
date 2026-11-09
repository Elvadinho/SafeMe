<?php

namespace Modules\Children\Services;

use App\Models\User;
use App\Services\FileService;
use Illuminate\Support\Facades\Log;
use Modules\Children\Enums\SightingStatus;
use Modules\Children\Models\MissingReport;
use Modules\Children\Models\Sighting;
use Throwable;

/**
 * "I saw this child" reports from citizens.
 * A sighting is PENDING until an admin confirms it: only confirmed sightings
 * move the public search zone (so a troll cannot send the search to the wrong city).
 */
class SightingService
{
    public function __construct(private FileService $files) {}

    public function submit(MissingReport $report, array $data, ?User $user): Sighting
    {
        // Only for active public alerts.
        abort_unless($report->status->isPublic(), 404);

        $photoPath = isset($data['photo'])
            ? $this->files->storePhoto($data['photo'], 'children/sightings')
            : null;

        try {
            $sighting = $report->sightings()->create([
                'lat'           => $data['lat'],
                'lng'           => $data['lng'],
                'seen_at'       => $data['seen_at'],
                'note'          => $data['note'] ?? null,
                'witness_phone' => $data['witness_phone'] ?? null,
                'photo_path'    => $photoPath,
                'user_id'       => $user?->id,
            ]);
        } catch (Throwable $e) {
            $this->files->delete([$photoPath]);
            throw $e;
        }

        // Phase 2: notify the family and the admins here (SMS / push).
        Log::info('Sighting submitted', ['report' => $report->public_id, 'sighting' => $sighting->id]);

        return $sighting;
    }

    public function confirm(Sighting $sighting): Sighting
    {
        return $this->changeStatus($sighting, SightingStatus::Confirmed);
    }

    public function dismiss(Sighting $sighting): Sighting
    {
        return $this->changeStatus($sighting, SightingStatus::Dismissed);
    }

    private function changeStatus(Sighting $sighting, SightingStatus $next): Sighting
    {
        abort_unless($sighting->status === SightingStatus::Pending, 409, 'This sighting was already reviewed.');

        $sighting->forceFill(['status' => $next])->save();

        return $sighting->makeVisible(['witness_phone']);
    }
}
