<?php

use Illuminate\Support\Facades\Route;
use Modules\TahunPelajaran\Http\Controllers\TahunPelajaranController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('tahunpelajarans', TahunPelajaranController::class)->names('tahunpelajaran');
});
