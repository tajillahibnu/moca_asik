<?php

use Illuminate\Support\Facades\Route;
use Modules\Kelas\Http\Controllers\KelasController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('kelas', KelasController::class)->names('kelas');
});
