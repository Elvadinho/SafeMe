<?php

use Illuminate\Support\Facades\Route;
use Modules\Children\Http\Controllers\ChildrenController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('childrens', ChildrenController::class)->names('children');
});
