<?php

namespace Modules\Children\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Children\Http\Requests\RejectReportRequest;
use Modules\Children\Models\MissingReport;
use Modules\Children\Models\ReportDocument;
use Modules\Children\Services\MissingReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Admins / police: review reports and their proof documents. */
class ReportModerationController extends Controller
{
    public function __construct(private MissingReportService $reports) {}

    /** GET /api/v1/admin/reports?status=pending */
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->reports->adminList($request->query('status')));
    }

    /** GET /api/v1/admin/reports/{report} */
    public function show(MissingReport $report): JsonResponse
    {
        return response()->json($this->reports->adminView($report));
    }

    /** GET /api/v1/admin/reports/{report}/documents/{document}: open a proof document. */
    public function document(MissingReport $report, ReportDocument $document): StreamedResponse
    {
        // The document must belong to THIS report (no reading other files by changing the id).
        abort_unless($document->missing_report_id === $report->id, 404);

        return Storage::disk(config('safeme.disk'))->response($document->path, null, [
            'Content-Type'  => $document->mime_type,
            'Cache-Control' => 'no-store', // never cache an ID card in the browser
        ]);
    }

    public function approve(Request $request, MissingReport $report): JsonResponse
    {
        return response()->json(['report' => $this->reports->approve($report, $request->user())]);
    }

    public function reject(RejectReportRequest $request, MissingReport $report): JsonResponse
    {
        return response()->json(['report' => $this->reports->reject($report, $request->user(), $request->input('reason'))]);
    }

    public function found(MissingReport $report): JsonResponse
    {
        return response()->json(['report' => $this->reports->markFound($report)]);
    }

    public function close(MissingReport $report): JsonResponse
    {
        return response()->json(['report' => $this->reports->close($report)]);
    }
}
