<?php

namespace Modules\Children\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Modules\Children\Models\Sighting;
use Modules\Children\Services\SightingService;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Admins / police: confirm or dismiss citizen sightings. */
class SightingModerationController extends Controller
{
    public function __construct(private SightingService $sightings) {}

    public function confirm(Sighting $sighting): JsonResponse
    {
        return response()->json(['sighting' => $this->sightings->confirm($sighting)]);
    }

    public function dismiss(Sighting $sighting): JsonResponse
    {
        return response()->json(['sighting' => $this->sightings->dismiss($sighting)]);
    }

    public function photo(Sighting $sighting): StreamedResponse
    {
        abort_unless($sighting->photo_path, 404);

        return Storage::disk(config('safeme.disk'))->response($sighting->photo_path, null, ['Cache-Control' => 'no-store']);
    }
}
