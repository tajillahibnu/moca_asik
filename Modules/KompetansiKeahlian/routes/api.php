<?php

use Illuminate\Support\Facades\Route;
use Modules\KompetansiKeahlian\Http\Controllers\KompetansiKeahlianController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('kompetansikeahlians', KompetansiKeahlianController::class)->names('kompetansikeahlian');
});
