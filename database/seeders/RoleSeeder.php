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

        // Tambahan role sesuai permintaan
        $siswaRole = $this->createRole('siswa', 'Siswa');
        $guruRole = $this->createRole('guru', 'Guru');
        $walikelasRole = $this->createRole('walikelas', 'Wali Kelas');
        $kesiswaanRole = $this->createRole('kesiswaan', 'Kesiswaan');
        $guruPendampingPklRole = $this->createRole('guru_pendamping_pkl', 'Guru Pendamping PKL');

        // Superadmin: all permissions
        $this->assignAllPermissions($superAdminRole);

        // Admin: all permissions except menu.*
        $this->assignAdminPermissions($adminRole);

        // User: only users.* (tanpa view users)
        $this->assignUserPermissions($userRole);

        // Siswa, Guru, Wali Kelas, Kesiswaan, Guru Pendamping PKL: 
        // Untuk sekarang, tidak diberikan permission khusus, 
        // bisa diatur sesuai kebutuhan di masa depan.
        // Contoh: $this->assignSiswaPermissions($siswaRole);
        //         $this->assignGuruPermissions($guruRole);
        //         dst.

        // Tambahan: assignAllPermissions untuk role lain jika diperlukan
        // Contoh penggunaan:
        // $this->assignAllPermissions($guruRole);
        // $this->assignAllPermissions($walikelasRole);
        // $this->assignAllPermissions($kesiswaanRole);
        // $this->assignAllPermissions($guruPendampingPklRole);
        $this->assignSiswaPermissions($siswaRole);
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

    /**
     * Assign only users.* (tanpa view users) to user.
     */
    protected function assignSiswaPermissions(Role $role)
    {
        // Ambil semua permission users.* kecuali user.view
        $permissions = Permission::where(function ($query) {
            $query->where('name', 'like', 'siswa.%')
                  ->where('name', '!=', 'user.view');
        })->get();

        $role->syncPermissions($permissions);
    }

    // Jika ingin menambah permission khusus untuk role baru, 
    // bisa tambahkan method assignSiswaPermissions, assignGuruPermissions, dst di sini.
}
