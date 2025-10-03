<?php

use Illuminate\Support\Facades\Route;
use Modules\Matapelajaran\Http\Controllers\MatapelajaranController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('matapelajarans', MatapelajaranController::class)->names('matapelajaran');
});
