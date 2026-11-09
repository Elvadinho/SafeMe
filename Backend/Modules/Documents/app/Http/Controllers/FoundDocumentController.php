<?php

namespace Modules\Documents\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Documents\Enums\DocumentKind;
use Modules\Documents\Http\Requests\PhoneCheckRequest;
use Modules\Documents\Http\Requests\StoreFoundDocumentRequest;
use Modules\Documents\Models\FoundDocument;
use Modules\Documents\Services\DocumentService;

class FoundDocumentController extends Controller
{
    public function __construct(private DocumentService $documents) {}

    /** GET /api/v1/found-documents?name=Anantia Temgoua&doc_type=national_id */
    public function index(Request $request): JsonResponse
    {
        // A name is required: nobody can browse the full list of found IDs.
        $validated = $request->validate([
            'name'     => ['required', 'string', 'min:3', 'max:120'],
            'doc_type' => ['nullable', Rule::enum(DocumentKind::class)],
        ]);

        $results = $this->documents->search($validated['name'], $validated['doc_type'] ?? null);

        return response()->json(['data' => $results->map->toPublicArray()->values()]);
    }

    /** POST /api/v1/found-documents: "I found a document". */
    public function store(StoreFoundDocumentRequest $request): JsonResponse
    {
        $found = $this->documents->reportFound($request->validated(), $request->user('sanctum'));

        return response()->json([
            'public_id' => $found->public_id, // the finder keeps it to follow their post
            'document'  => $found->toPublicArray(),
            'message'   => 'Thank you! We will contact you when the owner is identified.',
        ], 201);
    }

    /** POST /api/v1/found-documents/{foundDocument}/follow */
    public function follow(PhoneCheckRequest $request, FoundDocument $foundDocument): JsonResponse
    {
        return response()->json($this->documents->finderView($foundDocument, $request->input('phone')));
    }
}
