<?php

namespace Modules\Authentication\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Authentication\Http\Actions\Auth\SignInUserAction;
use Modules\Authentication\Http\Actions\Auth\RegisterUserAction;

class AuthenticationController extends Controller
{
    use ApiResponseTrait;

    public function login(Request $request)
    {
        try {
            // Semua pengaturan login (single/multi device) diatur di backend, tidak dari frontend
            // Selalu gunakan mode multi device dengan maksimal 3 device
            $request->merge([
                'login_type' => 'multi',
                'max_devices' => 3, // default 3, -1 = unlimited
            ]);

            $result = app(SignInUserAction::class)($request);

            return $this->success([
                'token' => $result['token'],
                'user' => $result['user'],
                // tambahan informasi perangkat & abilities sesuai SignInUserAction
                'active_devices' => $result['active_devices'] ?? [],
                'abilities' => $result['abilities'] ?? [],
            ], 'Login berhasil');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }

            return $this->success([
                'user' => $user,
            ], 'Berhasil logout.');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function user(Request $request)
    {
        try {
            $user = $request->user();
            if (method_exists($user, 'load')) {
                $user->load(['roles.permissions', 'permissions']);
            }

            return $this->success([
                'user' => $user,
                'abilities' => method_exists($user, 'getAbilities') ? $user->getAbilities() : [],
            ], 'Data user berhasil diambil.');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function register(Request $request)
    {
        try {
            $result = app(RegisterUserAction::class)($request->all());

            return $this->success($result, 'Registrasi berhasil', 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}