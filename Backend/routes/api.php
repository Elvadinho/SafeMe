<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Core routes. Every URL below starts with /api/v1.
// The Children and Documents modules add their own routes in Modules/*/routes/api.php.

Route::prefix('v1')->group(function () {

    // ── Authentication ─────────────────────────────────
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:login');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
    });

    // ── Super admin: manage accounts ───────────────────
    Route::middleware(['auth:sanctum', 'role:super_admin'])->prefix('admin')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::post('users/admins', [UserController::class, 'storeAdmin']);
        Route::patch('users/{user}/role', [UserController::class, 'updateRole']);
    });
});
