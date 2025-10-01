<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware EnsureTokenAbilities
 * 
 * Kegunaan file ini adalah untuk memastikan bahwa token yang digunakan pada request
 * memiliki kemampuan (abilities) tertentu sebelum mengizinkan akses ke route.
 * Jika token tidak memiliki salah satu dari abilities yang dibutuhkan, maka request akan
 * diblokir dan mengembalikan response JSON dengan pesan "Insufficient permissions."
 * 
 * Biasanya digunakan pada route yang membutuhkan otorisasi granular berbasis token abilities
 * pada Laravel Sanctum.
 */
class EnsureTokenAbilities
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$abilities): Response
    {
        foreach ($abilities as $ability) {
            if (!$request->user()->tokenCan($ability)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient permissions.'
                ], 403);
            }
        }
        return $next($request);
    }
}
