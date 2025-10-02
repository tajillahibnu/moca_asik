<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;


Route::get('/userx', function () {
    return [1, 2, 3];
});

// Sanctum route for SPA authentication (optional, for CSRF cookie)
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show'])->name('sanctum.csrf-cookie');

Route::prefix('auth')
    ->name('auth.')
    ->controller(AuthController::class)
    ->group(function () {
        Route::post('/login', 'login')->name('login');
        Route::post('/register', 'register')->name('register');
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', 'logout')->name('logout');
            Route::get('/user', 'user')->name('user');
        });
    });

// Protected user info route (optional, can be removed if /auth/user is used)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// User management routes, protected with sanctum and flexible permissions
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('user')
        ->name('user.')
        ->controller(\App\Http\Controllers\Api\UserController::class)
        ->group(function () {
            Route::middleware('permissions.flex:user.view')->get('/', 'index')->name('index');
            Route::middleware('permissions.flex:user.view')->get('/{user}', 'show')->name('show');
            // Route::middleware('permissions.flex:user.view|modifikasi')->get('/{user}', 'show')->name('show');
            Route::middleware('permissions.flex:create users')->post('/', 'store')->name('store');
            Route::middleware('permissions.flex:user.edit')->put('/{user}', 'update')->name('update');
            Route::middleware('permissions.flex:delete users')->delete('/{user}', 'destroy')->name('destroy');
        });
});

