<?php

namespace App\Http\Actions\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SignInUserAction
{
    /**
     * Menangani proses login user menggunakan Sanctum.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function execute(Request $request): array
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $credentials['email'])->first();

        // Jika user tidak ditemukan atau password salah
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Generate token autentikasi
        $token = $user->createAuthToken();

        // Kembalikan data user beserta token dan abilities
        return [
            'success' => true,
            'token' => 'Bearer ' . $token,
            'user' => $user->load('roles.permissions'),
            'abilities' => $user->getAbilities(),
        ];
    }
}
