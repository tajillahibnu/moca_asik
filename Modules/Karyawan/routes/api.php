<?php

use Illuminate\Support\Facades\Route;
use Modules\Karyawan\Http\Controllers\Api\KaryawanController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('karyawan')->name('karyawan.')
        ->controller(KaryawanController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::put('/{id}', 'update')->name('update');
            Route::patch('/{id}', 'update')->name('update.patch');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::post('/mass-destroy', 'massDestroy')->name('massDestroy');
        });
});
