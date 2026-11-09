<?php

namespace Modules\Documents\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Documents\Http\Requests\PhoneCheckRequest;
use Modules\Documents\Http\Requests\StoreLostDeclarationRequest;
use Modules\Documents\Models\LostDeclaration;
use Modules\Documents\Services\DocumentService;

class LostDeclarationController extends Controller
{
    public function __construct(private DocumentService $documents) {}

    /** POST /api/v1/lost-documents: "I lost my document". */
    public function store(StoreLostDeclarationRequest $request): JsonResponse
    {
        $lost = $this->documents->declareLost($request->validated());

        return response()->json([
            'public_id'    => $lost->public_id,
            'matches_found' => $lost->matches()->count(), // > 0 = someone already posted it!
            'message'      => 'Declaration saved. You will be notified if your document is found.',
        ], 201);
    }

    /** POST /api/v1/lost-documents/{lostDeclaration}/follow */
    public function follow(PhoneCheckRequest $request, LostDeclaration $lostDeclaration): JsonResponse
    {
        return response()->json($this->documents->ownerView($lostDeclaration, $request->input('phone')));
    }
}
