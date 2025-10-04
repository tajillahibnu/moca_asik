<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Matapelajaran\Models\Mapel;

class MapelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Master data mapel
        $data = [
            [
                'kode' => 'MTK',
                'nama' => 'Matematika',
                'nama_report' => 'Matematika',
                'parent_id' => null,
                'is_aktif' => true,
            ],
            [
                'kode' => 'BIND',
                'nama' => 'Bahasa Indonesia',
                'nama_report' => 'Bahasa Indonesia',
                'parent_id' => null,
                'is_aktif' => true,
            ],
            [
                'kode' => 'BING',
                'nama' => 'Bahasa Inggris',
                'nama_report' => 'Bahasa Inggris',
                'parent_id' => null,
                'is_aktif' => true,
            ],
            [
                'kode' => 'IPA',
                'nama' => 'Ilmu Pengetahuan Alam',
                'nama_report' => 'IPA',
                'parent_id' => null,
                'is_aktif' => true,
            ],
            [
                'kode' => 'IPS',
                'nama' => 'Ilmu Pengetahuan Sosial',
                'nama_report' => 'IPS',
                'parent_id' => null,
                'is_aktif' => true,
            ],
            [
                'kode' => 'PAI',
                'nama' => 'Pendidikan Agama Islam',
                'nama_report' => 'PAI',
                'parent_id' => null,
                'is_aktif' => true,
            ],
            [
                'kode' => 'PKN',
                'nama' => 'Pendidikan Kewarganegaraan',
                'nama_report' => 'PKN',
                'parent_id' => null,
                'is_aktif' => true,
            ],
            [
                'kode' => 'SENBUD',
                'nama' => 'Seni Budaya',
                'nama_report' => 'Seni Budaya',
                'parent_id' => null,
                'is_aktif' => true,
            ],
            [
                'kode' => 'PJOK',
                'nama' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan',
                'nama_report' => 'PJOK',
                'parent_id' => null,
                'is_aktif' => true,
            ],
            [
                'kode' => 'TIK',
                'nama' => 'Teknologi Informasi dan Komunikasi',
                'nama_report' => 'TIK',
                'parent_id' => null,
                'is_aktif' => true,
            ],
        ];

        foreach ($data as $item) {
            Mapel::updateOrCreate(
                ['kode' => $item['kode']],
                [
                    'nama' => $item['nama'],
                    'nama_report' => $item['nama_report'],
                    'parent_id' => $item['parent_id'],
                    'is_aktif' => $item['is_aktif'],
                ]
            );
        }
    }
}
