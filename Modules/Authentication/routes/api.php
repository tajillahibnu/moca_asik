<?php

use Illuminate\Support\Facades\Route;
use Modules\Authentication\Http\Controllers\Api\AuthenticationController;

Route::prefix('auth')
    ->name('auth.')
    ->controller(AuthenticationController::class)
    ->group(function () {
        Route::middleware('guest:sanctum')->group(function () {
            Route::post('/login', 'login')->name('login');
            Route::post('/register', 'register')->name('register');
        });
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', 'logout')->name('logout');
            Route::get('/user', 'user')->name('user');
        });
    });