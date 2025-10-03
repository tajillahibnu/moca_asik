<?php

use Illuminate\Support\Facades\Route;
use Modules\PKL\Http\Controllers\PKLController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('pkls', PKLController::class)->names('pkl');
});
