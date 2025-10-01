<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15); // default 15
            $query = User::with(['roles.permissions', 'permissions']);

            if ($search = $request->input('q')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            $users = $query->paginate($perPage);

            // Tambahkan meta pagination ke response
            return $this->apiResponse(['users' => $users->items()])
                ->addPaginationMeta($users)
                ->setMessage('Daftar user berhasil diambil.')
                ->send();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show($id)
    {
        try {
            $user = User::with(['roles.permissions', 'permissions'])->findOrFail($id);

            return $this->success(['user' => $user], 'User berhasil ditemukan.');
        } catch (ModelNotFoundException $e) {
            return $this->notFound('User tidak ditemukan.');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:6'],
                'roles' => ['sometimes', 'array'],
                'roles.*' => ['string'],
                'permissions' => ['sometimes', 'array'],
                'permissions.*' => ['string'],
            ], [
                'name.required' => 'Nama wajib diisi.',
                'name.string' => 'Nama harus berupa teks.',
                'name.max' => 'Nama maksimal 255 karakter.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email maksimal 255 karakter.',
                'email.unique' => 'Email sudah digunakan.',
                'password.required' => 'Password wajib diisi.',
                'password.string' => 'Password harus berupa teks.',
                'password.min' => 'Password minimal 6 karakter.',
                'roles.array' => 'Roles harus berupa array.',
                'roles.*.string' => 'Setiap role harus berupa string.',
                'permissions.array' => 'Permissions harus berupa array.',
                'permissions.*.string' => 'Setiap permission harus berupa string.',
            ]);

            // Gunakan action terpisah untuk pembuatan user
            $user = app(\App\Http\Actions\User\CreateUserAction::class)->execute($validated);

            return $this->success(['user' => $user], 'User berhasil ditambahkan.', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $id],
                'password' => ['sometimes', 'string', 'min:6'],
                'roles' => ['sometimes', 'array'],
                'roles.*' => ['string'],
                'permissions' => ['sometimes', 'array'],
                'permissions.*' => ['string'],
            ], [
                'name.string' => 'Nama harus berupa teks.',
                'name.max' => 'Nama maksimal 255 karakter.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email maksimal 255 karakter.',
                'email.unique' => 'Email sudah digunakan.',
                'password.string' => 'Password harus berupa teks.',
                'password.min' => 'Password minimal 6 karakter.',
                'roles.array' => 'Roles harus berupa array.',
                'roles.*.string' => 'Setiap role harus berupa string.',
                'permissions.array' => 'Permissions harus berupa array.',
                'permissions.*.string' => 'Setiap permission harus berupa string.',
            ]);

            // Gunakan action terpisah untuk update user
            $user = app(\App\Http\Actions\User\UpdateUserAction::class)->execute($id, $validated);

            return $this->success(['user' => $user], 'User berhasil diupdate.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('User tidak ditemukan.');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $user = \App\Models\User::findOrFail($id);

            // Optional: prevent deleting yourself
            if (Auth::id() === $user->id) {
                return $this->error('Anda tidak dapat menghapus akun Anda sendiri.', 403);
            }

            $user->delete();

            return $this->success(null, 'User berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('User tidak ditemukan.');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
