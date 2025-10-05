<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tingkat;

class TingkatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // SD
            [
                'nama' => 'I',
                'kode' => '1',
                'jenjang' => 'SD',
                'deskripsi' => 'Tingkat 1 SD',
            ],
            [
                'nama' => 'II',
                'kode' => '2',
                'jenjang' => 'SD',
                'deskripsi' => 'Tingkat 2 SD',
            ],
            [
                'nama' => 'III',
                'kode' => '3',
                'jenjang' => 'SD',
                'deskripsi' => 'Tingkat 3 SD',
            ],
            [
                'nama' => 'IV',
                'kode' => '4',
                'jenjang' => 'SD',
                'deskripsi' => 'Tingkat 4 SD',
            ],
            [
                'nama' => 'V',
                'kode' => '5',
                'jenjang' => 'SD',
                'deskripsi' => 'Tingkat 5 SD',
            ],
            [
                'nama' => 'VI',
                'kode' => '6',
                'jenjang' => 'SD',
                'deskripsi' => 'Tingkat 6 SD',
            ],
            // SMP
            [
                'nama' => 'VII',
                'kode' => '7',
                'jenjang' => 'SMP',
                'deskripsi' => 'Tingkat 7 SMP',
            ],
            [
                'nama' => 'VIII',
                'kode' => '8',
                'jenjang' => 'SMP',
                'deskripsi' => 'Tingkat 8 SMP',
            ],
            [
                'nama' => 'IX',
                'kode' => '9',
                'jenjang' => 'SMP',
                'deskripsi' => 'Tingkat 9 SMP',
            ],
            // SMA
            [
                'nama' => 'X',
                'kode' => '10',
                'jenjang' => 'SMA',
                'deskripsi' => 'Tingkat 10 SMA',
            ],
            [
                'nama' => 'XI',
                'kode' => '11',
                'jenjang' => 'SMA',
                'deskripsi' => 'Tingkat 11 SMA',
            ],
            [
                'nama' => 'XII',
                'kode' => '12',
                'jenjang' => 'SMA',
                'deskripsi' => 'Tingkat 12 SMA',
            ],
        ];

        foreach ($data as $tingkat) {
            Tingkat::updateOrCreate(
                ['kode' => $tingkat['kode']],
                $tingkat
            );
        }
    }
}
