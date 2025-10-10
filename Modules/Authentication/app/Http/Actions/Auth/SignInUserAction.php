<?php

namespace Modules\Authentication\Http\Actions\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use hisorange\BrowserDetect\Parser as Browser;

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
        // Validasi input request
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
            'login_type' => ['nullable','in:single,multi'],
            'max_devices' => ['nullable','integer'],
        ]);

        $loginType = $credentials['login_type'] ?? 'multi';
        $maxDevices = array_key_exists('max_devices', $credentials) ? (int)$credentials['max_devices'] : 3; // default 3, -1 = unlimited

        // Cari user berdasarkan email
        $user = User::where('email', $credentials['email'])->first();

        // Jika user tidak ditemukan atau password salah
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Ambil daftar token perangkat aktif: urut terbaru dulu, device metadata sudah di personal_access_tokens
        $activeDevices = [];
        if (method_exists($user, 'tokens')) {
            $tokens = $user->tokens()->latest()->get();
            foreach ($tokens as $token) {
                $activeDevices[] = [
                    'id' => $token->id,
                    'device_name' => $token->device_name,
                    'browser' => $token->browser,
                    'os' => $token->os,
                    'ip_address' => $token->ip_address,
                    'last_used_at' => $token->last_used_at,
                    'created_at' => $token->created_at,
                ];
            }
        }

        if ($loginType === 'single') {
            // Hanya boleh login di satu device, jika token sudah ada tolak
            if ($user->tokens()->exists()) {
                throw ValidationException::withMessages([
                    'email' => ['Akun ini sudah login di perangkat lain. Silakan logout dulu.'],
                    'active_devices' => $activeDevices,
                ]);
            }
            $token = $this->createTokenWithDeviceMeta($user, $request);
        } else {
            // Multi-device mode, cek limit device jika > 0 bukan unlimited
            if ($maxDevices > 0 && method_exists($user, 'tokens')) {
                $currentDevices = $user->tokens()->count();
                if ($currentDevices >= $maxDevices) {
                    throw ValidationException::withMessages([
                        'email' => [
                            "Maksimal perangkat login tercapai ({$maxDevices} device). Saat ini sudah login di {$currentDevices} perangkat. Silakan logout dari perangkat lain terlebih dahulu."
                        ],
                        'active_devices' => $activeDevices,
                    ]);
                }
            }
            $token = $this->createTokenWithDeviceMeta($user, $request);
        }

        return [
            'success' => true,
            'token' => 'Bearer ' . $token,
            'user' => $user->load('roles.permissions'),
            'abilities' => $user->getAbilities(),
            'active_devices' => $activeDevices,
        ];
    }

    /**
     * Membuat personal access token baru beserta metadata device/browser dari request.
     *
     * @param \App\Models\User $user
     * @param \Illuminate\Http\Request $request
     * @return string $plainToken
     */
    public function createTokenWithDeviceMeta(User $user, Request $request): string
    {
        $ip = $request->ip();
        $browser = Browser::browserFamily() ?: 'Unknown Browser';
        $os = Browser::platformFamily() ?: 'Unknown OS';
        $deviceName = Browser::deviceFamily() ?: 'Unknown Device';

        // Buat token dengan nama "Browser on OS"
        $plainToken = $user->createAuthToken($browser, $os);

        // Update entry terakhir (yang baru saja dibuat) pada tokens() dengan metadata device
        $latestToken = $user->tokens()->latest()->first();
        if ($latestToken) {
            $latestToken->forceFill([
                'device_name' => $deviceName,
                'browser' => $browser,
                'os' => $os,
                'ip_address' => $ip,
            ])->save();
        }

        return $plainToken;
    }
}
