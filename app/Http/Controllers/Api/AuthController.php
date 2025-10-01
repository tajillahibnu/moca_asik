<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $result = app(\App\Http\Actions\Auth\SignInUserAction::class)->execute($request);

            return response()->json($result);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            $user = $request->user()->load(['roles.permissions', 'permissions']);

            return response()->json([
                'success' => true,
                'user' => $user,
                'abilities' => $user->getAbilities(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User data error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $result = app(\App\Http\Actions\Auth\RegisterUserAction::class)(
                $request->all()
            );

            return response()->json($result, 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
