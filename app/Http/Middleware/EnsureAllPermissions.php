<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedException;
use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware Authorize
 * 
 * Fungsi:
 * Middleware ini digunakan untuk melakukan pengecekan otorisasi (authorization) pada route di aplikasi Laravel,
 * khususnya ketika Anda ingin memastikan bahwa user memiliki satu atau lebih permission tertentu secara bersamaan (AND condition).
 * 
 * Cara kerja:
 * - Middleware ini menerima string permission yang dipisahkan dengan tanda pipe (|), misal: "view_users|edit_users".
 * - User harus memiliki SEMUA permission yang disebutkan agar request dapat dilanjutkan.
 * - Jika salah satu permission tidak dimiliki user, maka akses akan ditolak dengan exception.
 * 
 * Kenapa harus ada:
 * - Dalam aplikasi modern, seringkali akses ke suatu resource membutuhkan lebih dari satu permission secara bersamaan.
 * - Middleware ini memudahkan developer untuk mendefinisikan kebutuhan otorisasi yang kompleks secara deklaratif langsung di route.
 * - Dengan adanya middleware ini, keamanan aplikasi lebih terjaga karena hanya user yang benar-benar memiliki semua permission yang dibutuhkan yang dapat mengakses resource tertentu.
 * 
 * Contoh penggunaan di route:
 * Route::middleware('authorize:view_users|edit_users')->get('/users', 'UserController@index');
 */
class EnsureAllPermissions
{
    /**
     * Handle authorization dengan multiple permissions (AND condition)
     * Usage: authorize:view_users|edit_users
     */
    public function handle(Request $request, Closure $next, string $permissions = null): Response
    {
        if ($permissions === null) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user) {
            throw new \Exception('Autentikasi diperlukan.');
        }

        $requiredPermissions = explode('|', $permissions);

        foreach ($requiredPermissions as $permission) {
            try {
                if (!$user->hasPermissionTo(trim($permission))) {
                    throw new \Exception("Anda tidak memiliki izin untuk mengakses resource ini. Dibutuhkan permission: {$permission}");
                }
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                throw new \Exception("Permission '{$permission}' tidak ditemukan.");
            }
        }

        return $next($request);
    }
}