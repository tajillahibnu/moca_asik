<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat 8 user random (tanpa role khusus)
        User::factory(8)->create();

        // Buat admin
        $adminRole = $this->getOrCreateRole('admin', 'Administrator');
        $adminUser = User::factory()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password123')
        ]);
        $adminUser->assignRole($adminRole);

        // // Buat user biasa
        // $userRole = $this->getOrCreateRole('user', 'User Biasa');
        // $regularUser = User::factory()->create([
        //     'name' => 'User Demo',
        //     'email' => 'user@demo.com',
        //     'password' => Hash::make('password123')
        // ]);
        // $regularUser->assignRole($userRole);

        // // Buat karyawan
        // $this->newKaryawan();
    }

    private function newKaryawan()
    {
        $karyawanRole = $this->getOrCreateRole('karyawan', 'Karyawan');

        for ($i = 1; $i <= 5; $i++) {
            $randomData = [
                'name' => "Karyawan Demo $i",
                'email' => "karyawan$i@demo.com",
                'password' => Hash::make('password123'),
            ];
            $karyawan = User::create($randomData);
            $karyawan->assignRole($karyawanRole);
        }
    }

    private function getOrCreateRole($name, $description)
    {
        return Role::firstOrCreate(
            [
                'name' => $name,
                'guard_name' => 'web'
            ],
            [
                'description' => $description
            ]
        );
    }
}
