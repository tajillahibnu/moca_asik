<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    protected $permissions = [
        'users' => [
            'view users' => 'View users list',
            'create users' => 'Create new users',
            'edit users' => 'Edit existing users',
            'delete users' => 'Delete users',
        ],
        'roles' => [
            'view roles' => 'View roles list',
            'create roles' => 'Create new roles',
            'edit roles' => 'Edit existing roles',
            'delete roles' => 'Delete roles',
        ],
        'permissions' => [
            'view permissions' => 'View permissions list',
            'edit permissions' => 'Edit permissions',
        ],
        'menus' => [
            'view menus' => 'View menus',
            'create menus' => 'Create menus',
            'edit menus' => 'Edit menus',
            'delete menus' => 'Delete menus',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // Create permissions
        foreach ($this->permissions as $group => $groupPermissions) {
            foreach ($groupPermissions as $name => $description) {
                // Convert "view users" => "user.view"
                $parts = explode(' ', $name, 2);
                if (count($parts) === 2) {
                    $action = $parts[0];
                    $entity = $parts[1];
                    // singularize entity if needed (optional, but keeping as is for now)
                    $permissionName = rtrim($entity, 's') . '.' . $action;
                } else {
                    $permissionName = str_replace(' ', '.', $name);
                }

                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web'
                ], [
                    // 'group' => $group,
                    'description' => $description
                ]);
            }
        }
    }
}
