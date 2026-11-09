<?php

namespace Modules\Documents\Services;

use App\Models\User;
use App\Support\Text;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Documents\Enums\FoundStatus;
use Modules\Documents\Enums\LostStatus;
use Modules\Documents\Enums\MatchStatus;
use Modules\Documents\Models\DocumentMatch;
use Modules\Documents\Models\FoundDocument;
use Modules\Documents\Models\LostDeclaration;

/**
 * Lost & found documents.
 * 1. A finder posts "I found X" / an owner posts "I lost X".
 * 2. The app compares type + normalized name and creates a PENDING match.
 * 3. An admin confirms the match → each side can see the other's contact.
 * Phone numbers are never public, at any step.
 */
class DocumentService
{
    // ── Finder side ────────────────────────────────────

    public function reportFound(array $data, ?User $user): FoundDocument
    {
        $found = FoundDocument::create([...$data, 'finder_user_id' => $user?->id]);

        $candidates = LostDeclaration::query()
            ->where('status', LostStatus::Open)
            ->where('doc_type', $found->doc_type)
            ->where('owner_name_key', $found->owner_name_key)
            ->get();

        foreach ($candidates as $lost) {
            $this->createMatchIfPlausible($found, $lost);
        }

        return $found;
    }

    /** What the finder sees about their post (with the post id + their phone). */
    public function finderView(FoundDocument $found, string $phone): array
    {
        abort_unless(hash_equals($found->finder_phone, $phone), 404);

        $found->load('matches.lostDeclaration');

        return [
            'document' => $found->toPublicArray(),
            'matches'  => $found->matches->map(fn (DocumentMatch $match) => [
                'status' => $match->status,
                // Contact only after an admin confirmed the owner
                'owner'  => $match->status === MatchStatus::Confirmed ? [
                    'name'  => $match->lostDeclaration->owner_name,
                    'phone' => $match->lostDeclaration->owner_phone,
                ] : null,
            ])->values(),
        ];
    }

    // ── Owner side ─────────────────────────────────────

    public function declareLost(array $data): LostDeclaration
    {
        $lost = LostDeclaration::create($data);

        $candidates = FoundDocument::query()
            ->where('status', FoundStatus::Available)
            ->where('doc_type', $lost->doc_type)
            ->where('owner_name_key', $lost->owner_name_key)
            ->get();

        foreach ($candidates as $found) {
            $this->createMatchIfPlausible($found, $lost);
        }

        return $lost;
    }

    /** What the owner sees about their declaration (with its id + their phone). */
    public function ownerView(LostDeclaration $lost, string $phone): array
    {
        abort_unless(hash_equals($lost->owner_phone, $phone), 404);

        $lost->load('matches.foundDocument');

        return [
            'declaration' => $lost->withoutRelations(),
            'matches'     => $lost->matches->map(fn (DocumentMatch $match) => [
                'status'   => $match->status,
                'document' => $match->foundDocument->toPublicArray(),
                'finder'   => $match->status === MatchStatus::Confirmed ? [
                    'name'  => $match->foundDocument->finder_name,
                    'phone' => $match->foundDocument->finder_phone,
                    'place' => $match->foundDocument->found_place,
                ] : null,
            ])->values(),
        ];
    }

    // ── Public search ──────────────────────────────────

    /** "Has someone found my document?" Search by full name (any order, accents ignored). */
    public function search(string $name, ?string $type): Collection
    {
        return FoundDocument::query()
            ->where('status', FoundStatus::Available)
            ->where('owner_name_key', Text::nameKey($name))
            ->when($type, fn ($query) => $query->where('doc_type', $type))
            ->latest('found_at')
            ->limit(20)
            ->get();
    }

    // ── Admin side ─────────────────────────────────────

    public function adminMatches(?string $status): LengthAwarePaginator
    {
        $status = MatchStatus::tryFrom((string) $status) ?? MatchStatus::Pending;

        return DocumentMatch::query()
            ->where('status', $status)
            ->with(['foundDocument', 'lostDeclaration'])
            ->oldest()
            ->paginate(20)
            ->through(function (DocumentMatch $match) {
                // Admins see everything, to compare the two sides.
                $match->foundDocument->makeVisible(['finder_name', 'finder_phone', 'number_last_digits']);
                $match->lostDeclaration->makeVisible(['owner_phone', 'number_last_digits']);

                return $match;
            });
    }

    public function confirm(DocumentMatch $match, User $admin): DocumentMatch
    {
        abort_unless($match->status === MatchStatus::Pending, 409, 'This match was already reviewed.');

        DB::transaction(function () use ($match, $admin) {
            $match->forceFill(['status' => MatchStatus::Confirmed, 'reviewed_by' => $admin->id, 'reviewed_at' => now()])->save();
            $match->foundDocument->forceFill(['status' => FoundStatus::Matched])->save();
            $match->lostDeclaration->forceFill(['status' => LostStatus::Matched])->save();

            // A document has one owner: other pending matches for it are rejected.
            DocumentMatch::query()
                ->where('found_document_id', $match->found_document_id)
                ->where('id', '!=', $match->id)
                ->where('status', MatchStatus::Pending)
                ->update(['status' => MatchStatus::Rejected, 'reviewed_by' => $admin->id, 'reviewed_at' => now()]);
        });

        // Phase 2: send an SMS to both sides here.
        Log::info('Document match confirmed', ['match' => $match->id]);

        return $match->refresh();
    }

    public function reject(DocumentMatch $match, User $admin): DocumentMatch
    {
        abort_unless($match->status === MatchStatus::Pending, 409, 'This match was already reviewed.');

        $match->forceFill(['status' => MatchStatus::Rejected, 'reviewed_by' => $admin->id, 'reviewed_at' => now()])->save();

        return $match;
    }

    public function markReturned(FoundDocument $found): FoundDocument
    {
        abort_unless($found->status === FoundStatus::Matched, 409, 'Only a matched document can be marked as returned.');

        $found->forceFill(['status' => FoundStatus::Returned])->save();

        return $found;
    }

    // ── Private helpers ────────────────────────────────

    /**
     * Same type + same name is already checked by the query.
     * Extra check: if BOTH sides gave the last digits of the number, they must be equal.
     */
    private function createMatchIfPlausible(FoundDocument $found, LostDeclaration $lost): void
    {
        $bothHaveDigits = $found->number_last_digits && $lost->number_last_digits;

        if ($bothHaveDigits && $found->number_last_digits !== $lost->number_last_digits) {
            return;
        }

        DocumentMatch::firstOrCreate([
            'found_document_id'   => $found->id,
            'lost_declaration_id' => $lost->id,
        ]);

        // Phase 2: notify the owner by SMS here ("a document with your name was found").
        Log::info('Possible document match', ['found' => $found->public_id, 'lost' => $lost->public_id]);
    }
}
