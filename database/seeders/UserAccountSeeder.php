<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
    }
}
