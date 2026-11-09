<?php

use Illuminate\Support\Facades\Route;
use Modules\Documents\Http\Controllers\Admin\DocumentMatchController;
use Modules\Documents\Http\Controllers\FoundDocumentController;
use Modules\Documents\Http\Controllers\LostDeclarationController;

// Every URL starts with /api/v1. No account needed for the public part.

Route::prefix('v1')->group(function () {

    // ── Found documents ────────────────────────────────
    Route::get('found-documents', [FoundDocumentController::class, 'index'])
        ->middleware('throttle:public-forms')->name('found-documents.index');
    Route::post('found-documents', [FoundDocumentController::class, 'store'])
        ->middleware('throttle:public-forms')->name('found-documents.store');
    Route::post('found-documents/{foundDocument}/follow', [FoundDocumentController::class, 'follow'])
        ->middleware('throttle:public-forms')->name('found-documents.follow');

    // ── Lost documents ─────────────────────────────────
    Route::post('lost-documents', [LostDeclarationController::class, 'store'])
        ->middleware('throttle:public-forms')->name('lost-documents.store');
    Route::post('lost-documents/{lostDeclaration}/follow', [LostDeclarationController::class, 'follow'])
        ->middleware('throttle:public-forms')->name('lost-documents.follow');

    // ── Admins ─────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('document-matches', [DocumentMatchController::class, 'index'])->name('document-matches.index');
        Route::post('document-matches/{match}/confirm', [DocumentMatchController::class, 'confirm'])->name('document-matches.confirm');
        Route::post('document-matches/{match}/reject', [DocumentMatchController::class, 'reject'])->name('document-matches.reject');
        Route::post('found-documents/{foundDocument}/returned', [DocumentMatchController::class, 'returned'])->name('found-documents.returned');
    });
});
