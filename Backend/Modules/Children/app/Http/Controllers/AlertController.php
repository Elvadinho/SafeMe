<?php

namespace Modules\Children\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Geo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Children\Http\Requests\StoreSightingRequest;
use Modules\Children\Models\MissingReport;
use Modules\Children\Services\MissingReportService;
use Modules\Children\Services\SightingService;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** The public side: approved alerts, the child's photo, and "I saw this child". */
class AlertController extends Controller
{
    public function __construct(
        private MissingReportService $reports,
        private SightingService $sightings,
    ) {}

    /** GET /api/v1/alerts?lat=3.84&lng=11.50&radius_km=20 */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat'       => [...Geo::latRules(false), 'required_with:lng'],
            'lng'       => [...Geo::lngRules(false), 'required_with:lat'],
            'radius_km' => ['nullable', 'numeric', 'between:1,500'],
        ]);

        $alerts = $this->reports->publicAlerts(
            isset($validated['lat']) ? (float) $validated['lat'] : null,
            isset($validated['lng']) ? (float) $validated['lng'] : null,
            (float) ($validated['radius_km'] ?? 50),
        );

        return response()->json(['data' => $alerts]);
    }

    /** GET /api/v1/alerts/{report} */
    public function show(MissingReport $report): JsonResponse
    {
        return response()->json($this->reports->publicAlert($report));
    }

    /**
     * GET /api/v1/alerts/{report}/photo
     * The photo is on the private disk. Public only when the alert is approved;
     * admins (with their token) can always see it, to verify pending reports.
     */
    public function photo(Request $request, MissingReport $report): StreamedResponse
    {
        $canSee = $report->status->isPublic() || $request->user('sanctum')?->canModerate();

        abort_unless($canSee, 404);

        return Storage::disk(config('safeme.disk'))->response($report->child_photo_path, null, [
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    /** POST /api/v1/alerts/{report}/sightings: "I saw this child". */
    public function storeSighting(StoreSightingRequest $request, MissingReport $report): JsonResponse
    {
        // Logged-in citizens are linked to their sighting; guests are welcome too.
        $sighting = $this->sightings->submit($report, $request->validated(), $request->user('sanctum'));

        return response()->json([
            'sighting' => $sighting,
            'message'  => 'Thank you. The family and the police have been informed.',
        ], 201);
    }
}
