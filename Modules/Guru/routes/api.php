<?php

use Illuminate\Support\Facades\Route;
use Modules\Guru\Http\Controllers\Api\GuruController;

Route::middleware(['auth:sanctum'])->name('guru.')->prefix('guru')->group(function () {
    Route::get('/', [GuruController::class, 'index'])->name('index');
    Route::post('/', [GuruController::class, 'store'])->name('store');
    Route::get('/{id}', [GuruController::class, 'show'])->name('show');
    Route::put('/{id}', [GuruController::class, 'update'])->name('update');
    Route::patch('/{id}', [GuruController::class, 'update'])->name('update');
    Route::delete('/{id}', [GuruController::class, 'destroy'])->name('destroy');
    Route::post('/mass-destroy', [GuruController::class, 'massDestroy'])->name('massDestroy');
});
