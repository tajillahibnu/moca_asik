<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Modules\Guru\Models\Guru;

class GuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role guru ada
        $guruRole = Role::firstOrCreate(
            [
                'name' => 'guru',
                'guard_name' => 'web'
            ],
            [
                'description' => 'Guru'
            ]
        );

        // Data guru, sesuaikan dengan kebutuhan dan struktur tabel
        $gurus = [
            [
                'name' => 'Ahmad Suryana',
                'email' => 'ahmad.suryana@demo.com',
                'password' => 'password123',
                'nip' => '198012310001',
                'nuptk' => '1234567890001',
                'no_hp' => '081234567801',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1980-12-31',
                'alamat' => 'Jl. Merdeka No. 1, Bandung',
                'foto' => null,
                'jabatan' => 'Guru Matematika',
                'is_aktif' => true,
            ],
            [
                'name' => 'Siti Rahmawati',
                'email' => 'siti.rahmawati@demo.com',
                'password' => 'password123',
                'nip' => '198105150002',
                'nuptk' => '1234567890002',
                'no_hp' => '081234567802',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1981-05-15',
                'alamat' => 'Jl. Sudirman No. 2, Jakarta',
                'foto' => null,
                'jabatan' => 'Guru Bahasa Indonesia',
                'is_aktif' => true,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@demo.com',
                'password' => 'password123',
                'nip' => '198203200003',
                'nuptk' => '1234567890003',
                'no_hp' => '081234567803',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Surabaya',
                'tanggal_lahir' => '1982-03-20',
                'alamat' => 'Jl. Pahlawan No. 3, Surabaya',
                'foto' => null,
                'jabatan' => 'Guru IPA',
                'is_aktif' => true,
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@demo.com',
                'password' => 'password123',
                'nip' => '198304100004',
                'nuptk' => '1234567890004',
                'no_hp' => '081234567804',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Yogyakarta',
                'tanggal_lahir' => '1983-04-10',
                'alamat' => 'Jl. Malioboro No. 4, Yogyakarta',
                'foto' => null,
                'jabatan' => 'Guru IPS',
                'is_aktif' => true,
            ],
            [
                'name' => 'Rizky Pratama',
                'email' => 'rizky.pratama@demo.com',
                'password' => 'password123',
                'nip' => '198405250005',
                'nuptk' => '1234567890005',
                'no_hp' => '081234567805',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Semarang',
                'tanggal_lahir' => '1984-05-25',
                'alamat' => 'Jl. Pandanaran No. 5, Semarang',
                'foto' => null,
                'jabatan' => 'Guru Bahasa Inggris',
                'is_aktif' => true,
            ],
        ];

        foreach ($gurus as $guruData) {
            // Buat user
            $user = \App\Models\User::create([
                'name' => $guruData['name'],
                'email' => $guruData['email'],
                'password' => Hash::make($guruData['password']),
                'source' => 'guru',
            ]);

            // Buat guru dan relasikan ke user
            $guru = Guru::create([
                'name' => $guruData['name'],
                'email' => $guruData['email'],
                'nip' => $guruData['nip'],
                'nuptk' => $guruData['nuptk'],
                'no_hp' => $guruData['no_hp'],
                'jenis_kelamin' => $guruData['jenis_kelamin'],
                'tempat_lahir' => $guruData['tempat_lahir'],
                'tanggal_lahir' => $guruData['tanggal_lahir'],
                'alamat' => $guruData['alamat'],
                'foto' => $guruData['foto'],
                'jabatan' => $guruData['jabatan'],
                'is_aktif' => $guruData['is_aktif'],
                'user_id' => $user->id,
            ]);

            // Assign role guru ke user
            $user->assignRole($guruRole);
        }
    }
}
