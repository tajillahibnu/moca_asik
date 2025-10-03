<?php

use Illuminate\Support\Facades\Route;
use Modules\Kelas\Http\Controllers\KelasController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('kelas', KelasController::class)->names('kelas');
});
