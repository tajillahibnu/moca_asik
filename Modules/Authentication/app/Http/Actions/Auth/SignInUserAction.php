<?php

namespace Modules\Authentication\Http\Actions\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SignInUserAction
{
    /**
     * Menangani proses login user menggunakan Sanctum.
     * Mendukung dua tipe login:
     * 1. Multi device (bisa dibatasi jumlah device, default 3 device, atau tanpa batasan jika max_devices=-1)
     * 2. Single login (hanya bisa login di satu device, token sebelumnya dihapus)
     *
     * Tambahkan parameter login_type pada request: "single" | "multi"
     * Untuk multi: tambahkan max_devices (int, optional, default: 3, -1 artinya tak terbatas)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __invoke(Request $request): array
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'login_type' => 'nullable|in:single,multi',
            'max_devices' => 'nullable|integer',
        ]);

        $loginType = $credentials['login_type'] ?? 'multi';
        $maxDevices = isset($credentials['max_devices']) ? (int)$credentials['max_devices'] : 3; // default 3, -1 = unlimited

        // Cari user berdasarkan email
        $user = User::where('email', $credentials['email'])->first();

        // Jika user tidak ditemukan atau password salah
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if ($loginType === 'single') {
            // Hapus semua token lama milik user (hanya bisa login 1 device saja)
            if ($user->tokens()->exists()) {
                throw ValidationException::withMessages([
                    'email' => ['Akun ini sudah login di perangkat lain. Silakan logout dulu.'],
                ]);
            }    
            $token = $user->createAuthToken();
        } else {
            // Multi device mode
            if ($maxDevices > 0 && method_exists($user, 'tokens')) {
                $tokens = $user->tokens()->latest()->get();
                $currentDevices = $tokens->count();
                if ($currentDevices >= $maxDevices) {
                    throw ValidationException::withMessages([
                        'email' => [
                            "Maksimal perangkat login tercapai ({$maxDevices} device). Saat ini sudah login di {$currentDevices} perangkat. Silakan logout dari perangkat lain terlebih dahulu."
                        ],
                    ]);
                }
            }
            $token = $user->createAuthToken();
        }

        return [
            'success' => true,
            'token' => 'Bearer ' . $token,
            'user' => $user->load('roles.permissions'),
            'abilities' => $user->getAbilities(),
        ];
    }
}
