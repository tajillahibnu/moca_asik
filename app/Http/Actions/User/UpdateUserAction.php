<?php

namespace App\Http\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UpdateUserAction
{
    /**
     * Update user data, roles, and permissions.
     *
     * @param int $id
     * @param array $data
     * @return User
     * @throws ValidationException
     */
    public function execute(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $user = User::findOrFail($id);

            // Update user fields
            $updateFields = [];
            if (isset($data['name'])) {
                $updateFields['name'] = $data['name'];
            }
            if (isset($data['email'])) {
                $updateFields['email'] = $data['email'];
            }
            if (isset($data['password']) && $data['password']) {
                $updateFields['password'] = Hash::make($data['password']);
            }
            if (!empty($updateFields)) {
                $user->update($updateFields);
            }

            // Update roles: old roles will be removed, only new roles will be assigned
            if (isset($data['roles']) && is_array($data['roles'])) {
                $roleIds = [];
                foreach ($data['roles'] as $roleName) {
                    $role = Role::firstOrCreate(['name' => $roleName]);
                    $roleIds[] = $role->id;
                }
                // Sync will remove old roles and assign only the new ones
                $user->roles()->sync($roleIds);
            } elseif (array_key_exists('roles', $data) && (is_array($data['roles']) && count($data['roles']) === 0)) {
                // If roles is an empty array, remove all roles
                $user->roles()->detach();
            }

            // Update permissions: old permissions will be removed, only new permissions will be assigned
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $user->syncPermissions($data['permissions']);
            } elseif (array_key_exists('permissions', $data) && (is_array($data['permissions']) && count($data['permissions']) === 0)) {
                // If permissions is an empty array, remove all permissions
                $user->syncPermissions([]);
            }

            return $user->load(['roles.permissions', 'permissions']);
        });
    }
}

