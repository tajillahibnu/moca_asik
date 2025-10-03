<?php

use Illuminate\Support\Facades\Route;
use Modules\PKL\Http\Controllers\PKLController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('pkls', PKLController::class)->names('pkl');
});
