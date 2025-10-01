<?php

namespace App\Http\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class CreateUserAction
{
    /**
     * Create a new user with optional roles and permissions.
     *
     * @param array $data
     * @return User
     * @throws ValidationException
     */
    public function execute(array $data)
    {
        // Validasi sederhana, sebaiknya gunakan FormRequest di Controller untuk validasi lebih baik
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            throw ValidationException::withMessages([
                'name' => 'Name, email, and password are required.'
            ]);
        }

        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Tentukan role yang digunakan, jika tidak ada, default ke 'user'
            $roles = [];
            if (!empty($data['roles']) && is_array($data['roles'])) {
                $roles = $data['roles'];
            } else {
                $roles = ['user'];
            }

            foreach ($roles as $roleName) {
                $role = Role::firstOrCreate(['name' => $roleName]);
                $user->assignRole($role);
            }

            // Assign permissions langsung ke user jika ada
            if (!empty($data['permissions']) && is_array($data['permissions'])) {
                $user->givePermissionTo($data['permissions']);
            }

            return $user->load(['roles.permissions', 'permissions']);
        });
    }
}
