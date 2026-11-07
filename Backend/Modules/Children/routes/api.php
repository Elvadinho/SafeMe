<?php

use Illuminate\Support\Facades\Route;
use Modules\Children\Http\Controllers\ChildrenController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('childrens', ChildrenController::class)->names('children');
});
