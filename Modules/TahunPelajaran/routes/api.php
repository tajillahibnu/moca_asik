<?php

use Illuminate\Support\Facades\Route;
use Modules\TahunPelajaran\Http\Controllers\TahunPelajaranController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('tahunpelajarans', TahunPelajaranController::class)->names('tahunpelajaran');
});
