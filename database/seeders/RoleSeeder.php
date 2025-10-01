<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = $this->createRole('superadmin', 'Super Admin with highest privileges');
        $adminRole = $this->createRole('admin', 'Administrator with full access');
        $userRole = $this->createRole('user', 'User with basic access');

        // Superadmin: all permissions
        $this->assignAllPermissions($superAdminRole);

        // Admin: all permissions except menu.*
        $this->assignAdminPermissions($adminRole);

        // User: only users.* (tanpa view users)
        $this->assignUserPermissions($userRole);
    }

    /**
     * Create a role if it does not exist.
     */
    protected function createRole(string $name, string $description, bool $isDefault = false)
    {
        return Role::firstOrCreate(
            [
                'name' => $name,
                'guard_name' => 'web'
            ],
            [
                'description' => $description,
                'is_default' => $isDefault
            ]
        );
    }

    /**
     * Assign all permissions to a role.
     */
    protected function assignAllPermissions(Role $role)
    {
        $role->syncPermissions(Permission::all());
    }

    /**
     * Assign all permissions except menu.* and permission.* to admin.
     */
    protected function assignAdminPermissions(Role $role)
    {
        $permissions = Permission::where(function ($query) {
            $query->where('name', 'not like', 'menu.%')
                  ->where('name', 'not like', 'permission.%');
        })->get();

        $role->syncPermissions($permissions);
    }

    /**
     * Assign only users.* (tanpa view users) to user.
     */
    protected function assignUserPermissions(Role $role)
    {
        // Ambil semua permission users.* kecuali user.view
        $permissions = Permission::where(function ($query) {
            $query->where('name', 'like', 'user.%')
                  ->where('name', '!=', 'user.view');
        })->get();

        $role->syncPermissions($permissions);
    }
}
