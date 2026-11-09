<?php

use Illuminate\Support\Facades\Route;
use Modules\Children\Http\Controllers\Admin\ReportModerationController;
use Modules\Children\Http\Controllers\Admin\SightingModerationController;
use Modules\Children\Http\Controllers\AlertController;
use Modules\Children\Http\Controllers\ReportController;

// Every URL starts with /api/v1 ("api" comes from the module RouteServiceProvider).
// Route names start with "api." (e.g. api.alerts.photo).

Route::prefix('v1')->group(function () {

    // ── Reporter (parent / guardian), no account ───────
    Route::post('reports', [ReportController::class, 'store'])
        ->middleware('throttle:reports')->name('reports.store');
    Route::post('reports/{report}/follow', [ReportController::class, 'follow'])
        ->middleware('throttle:public-forms')->name('reports.follow');
    Route::post('reports/{report}/documents', [ReportController::class, 'addDocuments'])
        ->middleware('throttle:public-forms')->name('reports.documents.store');

    // ── Public alerts ──────────────────────────────────
    Route::get('alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('alerts/{report}', [AlertController::class, 'show'])->name('alerts.show');
    Route::get('alerts/{report}/photo', [AlertController::class, 'photo'])->name('alerts.photo');
    Route::post('alerts/{report}/sightings', [AlertController::class, 'storeSighting'])
        ->middleware('throttle:public-forms')->name('alerts.sightings.store');

    // ── Admins / police ────────────────────────────────
    Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('reports', [ReportModerationController::class, 'index'])->name('reports.index');
        Route::get('reports/{report}', [ReportModerationController::class, 'show'])->name('reports.show');
        Route::get('reports/{report}/documents/{document}', [ReportModerationController::class, 'document'])->name('reports.document');
        Route::post('reports/{report}/approve', [ReportModerationController::class, 'approve'])->name('reports.approve');
        Route::post('reports/{report}/reject', [ReportModerationController::class, 'reject'])->name('reports.reject');
        Route::post('reports/{report}/found', [ReportModerationController::class, 'found'])->name('reports.found');
        Route::post('reports/{report}/close', [ReportModerationController::class, 'close'])->name('reports.close');

        Route::post('sightings/{sighting}/confirm', [SightingModerationController::class, 'confirm'])->name('sightings.confirm');
        Route::post('sightings/{sighting}/dismiss', [SightingModerationController::class, 'dismiss'])->name('sightings.dismiss');
        Route::get('sightings/{sighting}/photo', [SightingModerationController::class, 'photo'])->name('sightings.photo');
    });
});
