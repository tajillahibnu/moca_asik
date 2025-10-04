<?php

use Illuminate\Support\Facades\Route;
use Modules\KompetensiKeahlian\Http\Controllers\Api\KompetensiKeahlianController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('kompetensi-keahlian', KompetensiKeahlianController::class)
        ->names('kompetensi-keahlian');
});
