<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Karyawan\Models\Karyawan;
use App\Models\User;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role guru ada
        $guruRole = Role::firstOrCreate(
            [
                'name' => 'karyawan',
                'guard_name' => 'web'
            ],
            [
                'description' => 'Karyawan'
            ]
        );
        // Contoh data karyawan
        $karyawans = [
            [
                'name' => 'Budi Santoso',
                'nip' => 'KAR001',
                'email' => 'budi@example.com',
                'no_hp' => '081234567890',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1990-01-01',
                'alamat' => 'Jl. Merdeka No. 1',
                'jabatan' => 'Manager',
                'is_aktif' => 1,
                'password' => 'password123',
            ],
            [
                'name' => 'Siti Aminah',
                'nip' => 'KAR002',
                'email' => 'siti@example.com',
                'no_hp' => '081298765432',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1992-05-10',
                'alamat' => 'Jl. Sudirman No. 2',
                'jabatan' => 'Staff',
                'is_aktif' => 1,
                'password' => 'password123',
            ],
        ];

        foreach ($karyawans as $data) {
            // Buat user terlebih dahulu
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Assign role karyawan jika menggunakan spatie/permission
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $user->assignRole('karyawan');
            }

            // Buat karyawan dan hubungkan dengan user
            Karyawan::create([
                'name' => $data['name'],
                'nip' => $data['nip'],
                'email' => $data['email'],
                'no_hp' => $data['no_hp'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'alamat' => $data['alamat'],
                'jabatan' => $data['jabatan'],
                'is_aktif' => $data['is_aktif'],
                'user_id' => $user->id,
            ]);
        }
    }
}
