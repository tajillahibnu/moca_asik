<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Modules\Siswa\Models\Siswa;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat role siswa jika belum ada
        $siswaRole = Role::firstOrCreate(
            [
                'name' => 'siswa',
                'guard_name' => 'web'
            ],
            [
                'description' => 'Siswa'
            ]
        );

        // Buat 10 data siswa
        for ($i = 1; $i <= 10; $i++) {
            $tahun = date('Y');
            $npsn = config('sekolah.npsn', env('NPSN', '00000000'));
            $noUrut = $i;
            $nis = "{$npsn}." . str_pad($noUrut, 4, '0', STR_PAD_LEFT) . "." . str_pad($tahun, 4, '0', STR_PAD_LEFT);

            $user = User::create([
                'name' => "Siswa Demo $i",
                'email' => "siswa$i@demo.com",
                'password' => Hash::make('password123'),
                'source' => 'siswa',
            ]);

            $siswa = Siswa::create([
                'name' => "Siswa Demo $i",
                'email' => "siswa$i@demo.com",
                'nis' => $nis,
                'user_id' => $user->id,
            ]);

            $user->assignRole($siswaRole);
        }
    }
}
