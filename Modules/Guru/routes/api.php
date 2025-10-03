<?php

use Illuminate\Support\Facades\Route;
use Modules\Guru\Http\Controllers\GuruController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('gurus', GuruController::class)->names('guru');
});
