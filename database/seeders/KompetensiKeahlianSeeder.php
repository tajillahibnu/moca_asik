<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Guru\Models\Guru;
use Modules\KompetensiKeahlian\Models\KompetensiKeahlian;
use Modules\KompetensiKeahlian\Models\KomptAhli;

class KompetensiKeahlianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua guru yang ada
        $gurus = Guru::all();

        // Daftar nama kompetensi keahlian/jurusan contoh
        $kompetensiList = [
            ['nama' => 'Teknik Komputer dan Jaringan', 'kode' => 'TKJ', 'deskripsi' => 'Jurusan yang mempelajari tentang komputer dan jaringan.'],
            ['nama' => 'Rekayasa Perangkat Lunak', 'kode' => 'RPL', 'deskripsi' => 'Jurusan yang mempelajari tentang pengembangan perangkat lunak.'],
            ['nama' => 'Teknik Kendaraan Ringan', 'kode' => 'TKR', 'deskripsi' => 'Jurusan yang mempelajari tentang kendaraan ringan.'],
            ['nama' => 'Akuntansi', 'kode' => 'AK', 'deskripsi' => 'Jurusan yang mempelajari tentang akuntansi dan keuangan.'],
            ['nama' => 'Administrasi Perkantoran', 'kode' => 'AP', 'deskripsi' => 'Jurusan yang mempelajari tentang administrasi perkantoran.'],
        ];

        $i = 0;
        foreach ($kompetensiList as $kompetensi) {
            // Pilih kepala jurusan dari guru secara berurutan, jika guru kurang, gunakan null
            $kepalaJurusan = $gurus->get($i) ?? null;

            KomptAhli::create([
                'public_url_code'    => Str::uuid(),
                'nama'               => $kompetensi['nama'],
                'kode'               => $kompetensi['kode'],
                'slug'               => Str::slug($kompetensi['nama']),
                'deskripsi'          => $kompetensi['deskripsi'],
                'kepala_jurusan_id'  => $kepalaJurusan ? $kepalaJurusan->id : null,
                'is_aktif'           => true,
                'created_by'         => 1, // default admin
            ]);
            $i++;
        }
    }
}
