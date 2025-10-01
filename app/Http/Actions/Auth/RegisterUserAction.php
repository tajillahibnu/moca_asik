<?php

namespace App\Http\Actions\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterUserAction
{
    /**
     * Menangani proses registrasi user.
     *
     * @param  array  $data
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __invoke(array $data): array
    {
        // Validasi input
        $validated = $this->validateData($data);

        // Buat user baru
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

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

    /**
     * Validasi data pendaftaran user.
     *
     * @param array $data
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateData(array $data): array
    {
        $validator = validator($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
