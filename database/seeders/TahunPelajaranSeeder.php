<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\TahunPelajaran\Models\TahunPelajaran;

class TahunPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama' => '2020/2021',
                'is_aktif' => false,
            ],
            [
                'nama' => '2021/2022',
                'is_aktif' => false,
            ],
            [
                'nama' => '2022/2023',
                'is_aktif' => false,
            ],
            [
                'nama' => '2023/2024',
                'is_aktif' => false,
            ],
            [
                'nama' => '2024/2025',
                'is_aktif' => true,
            ],
        ];

        foreach ($data as $item) {
            TahunPelajaran::updateOrCreate(
                ['nama' => $item['nama']],
                ['is_aktif' => $item['is_aktif']]
            );
        }
    }
}
