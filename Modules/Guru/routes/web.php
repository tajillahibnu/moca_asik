<?php

use Illuminate\Support\Facades\Route;
use Modules\Guru\Http\Controllers\GuruController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('gurus', GuruController::class)->names('guru');
});
