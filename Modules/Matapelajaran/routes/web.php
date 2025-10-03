<?php

use Illuminate\Support\Facades\Route;
use Modules\Matapelajaran\Http\Controllers\MatapelajaranController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('matapelajarans', MatapelajaranController::class)->names('matapelajaran');
});
