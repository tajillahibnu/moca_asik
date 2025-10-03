<?php

use Illuminate\Support\Facades\Route;
use Modules\KompetansiKeahlian\Http\Controllers\KompetansiKeahlianController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('kompetansikeahlians', KompetansiKeahlianController::class)->names('kompetansikeahlian');
});
