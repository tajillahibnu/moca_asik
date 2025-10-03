<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Siswa\Http\Action\Siswa\CreateSiswaAction;
use Spatie\Permission\Models\Role;

class UserAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(8)->create();

        $adminUser = User::factory()->create([
            'name' => 'Admin Demo',
            "email" => "admin@demo.com",
            "password" => "password123"
        ]);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminUser->assignRole($adminRole);

        $regularUser = User::factory()->create([
            'name' => 'User Demo',
            "email" => "user@demo.com",
            "password" => "password123"
        ]);
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $regularUser->assignRole($userRole);
        $this->newSiswa();
    }

    private function newSiswa()
    {
        // Buat akun user biasa
        $userRole = Role::firstOrCreate(['name' => 'siswa']);

        // Buat 8 akun siswa random
        for ($i = 1; $i <= 8; $i++) {
            $randomData = [
                'nama_lengkap' => "Siswa Demo $i",
                'email' => "siswa$i@demo.com",
                'password' => 'password123',
            ];
            $siswa = app(CreateSiswaAction::class)($randomData);
            $siswa->user->assignRole($userRole);
        }
    }
}
