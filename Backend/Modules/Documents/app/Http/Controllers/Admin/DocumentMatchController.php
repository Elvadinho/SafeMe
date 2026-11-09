<?php

namespace Modules\Documents\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Documents\Models\DocumentMatch;
use Modules\Documents\Models\FoundDocument;
use Modules\Documents\Services\DocumentService;

/** Admins: confirm that a found document really belongs to the person who declared it lost. */
class DocumentMatchController extends Controller
{
    public function __construct(private DocumentService $documents) {}

    /** GET /api/v1/admin/document-matches?status=pending */
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->documents->adminMatches($request->query('status')));
    }

    public function confirm(Request $request, DocumentMatch $match): JsonResponse
    {
        return response()->json(['match' => $this->documents->confirm($match, $request->user())]);
    }

    public function reject(Request $request, DocumentMatch $match): JsonResponse
    {
        return response()->json(['match' => $this->documents->reject($match, $request->user())]);
    }

    public function returned(FoundDocument $foundDocument): JsonResponse
    {
        return response()->json(['document' => $this->documents->markReturned($foundDocument)->toPublicArray()]);
    }
}
