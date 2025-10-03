<?php

use Illuminate\Support\Facades\Route;
use Modules\Siswa\Http\Controllers\Api\SiswaController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('siswa')
        ->name('siswa.')
        ->controller(SiswaController::class)
        ->group(function () {
            // Urutan berdasarkan penggunaan umum:
            Route::get('/', 'index')->name('index'); // List siswa
            Route::post('/', 'store')->name('store'); // Tambah siswa
            Route::get('/{id}', 'show')->name('show'); // Detail siswa
            Route::put('/{id}', 'update')->name('update'); // Update siswa
            Route::delete('/{id}', 'destroy')->name('destroy'); // Hapus siswa
            Route::post('/mass-destroy', 'massDestroy')->name('massDestroy'); // Hapus massal
        });
});
