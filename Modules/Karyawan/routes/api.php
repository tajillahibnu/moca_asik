<?php

use Illuminate\Support\Facades\Route;
use Modules\Karyawan\Http\Controllers\KaryawanController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('karyawans', KaryawanController::class)->names('karyawan');
});
