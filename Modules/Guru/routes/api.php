<?php

use Illuminate\Support\Facades\Route;
use Modules\Guru\Http\Controllers\Api\GuruController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('guru')->name('guru.')
        ->controller(GuruController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::put('/{id}', 'update')->name('update');
            Route::patch('/{id}', 'update')->name('update.patch');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::post('/mass-destroy', 'massDestroy')->name('massDestroy');
        });
});
