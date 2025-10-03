<?php

use Illuminate\Support\Facades\Route;
use Modules\Karyawan\Http\Controllers\KaryawanController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('karyawans', KaryawanController::class)->names('karyawan');
});
