<?php

namespace Modules\Children\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Children\Http\Requests\ReporterRequest;
use Modules\Children\Http\Requests\StoreReportRequest;
use Modules\Children\Models\MissingReport;
use Modules\Children\Services\MissingReportService;

/** The reporter (parent / guardian) side. No account needed. */
class ReportController extends Controller
{
    public function __construct(private MissingReportService $reports) {}

    /** POST /api/v1/reports: report a missing child. */
    public function store(StoreReportRequest $request): JsonResponse
    {
        $report = $this->reports->submit($request->validated());

        return response()->json([
            'public_id'         => $report->public_id, // the parent keeps this to follow the case
            'status'            => $report->status,
            'missing_documents' => array_map(fn ($type) => $type->value, $report->missingDocumentTypes()),
            'message'           => 'Report received. An officer will call you to verify before the alert is published.',
        ], 201);
    }

    /** POST /api/v1/reports/{report}/follow: the reporter follows their case. */
    public function follow(ReporterRequest $request, MissingReport $report): JsonResponse
    {
        $this->reports->ensureReporter($report, $request->input('reporter_phone'));

        return response()->json($this->reports->reporterView($report));
    }

    /** POST /api/v1/reports/{report}/documents: add proof documents later. */
    public function addDocuments(ReporterRequest $request, MissingReport $report): JsonResponse
    {
        $this->reports->ensureReporter($report, $request->input('reporter_phone'));

        $report = $this->reports->addDocuments($report, $request->validated('documents'));

        return response()->json([
            'documents'         => $report->documents,
            'missing_documents' => array_map(fn ($type) => $type->value, $report->missingDocumentTypes()),
        ], 201);
    }
}
