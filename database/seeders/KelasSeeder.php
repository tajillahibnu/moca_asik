<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Kelas\Models\Kelas;
use App\Models\Tingkat;
use Modules\KompetensiKeahlian\Models\KomptAhli;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Guru\Models\Guru;
use Modules\Siswa\Models\Siswa;
use Modules\Kelas\Models\SiswaKelas;
use Modules\Kelas\Models\RiwayatWalikelas;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua tingkat SMK (X, XI, XII)
        $tingkatX   = Tingkat::where('nama', 'X')->first();
        $tingkatXI  = Tingkat::where('nama', 'XI')->first();
        $tingkatXII = Tingkat::where('nama', 'XII')->first();

        // Ambil semua kompetensi keahlian
        $komptAhlis = KomptAhli::all();

        // Daftar kode jurusan yang ingin dibuatkan kelas
        $jurusanList = ['TKJ', 'RPL'];

        $kelasList = [];

        foreach ($jurusanList as $kodeJurusan) {
            $komptAhli = $komptAhlis->where('kode', $kodeJurusan)->first();
            if (!$komptAhli) {
                continue;
            }

            // Kelas X
            $kelasList[] = [
                'tingkat_id'      => $tingkatX ? $tingkatX->id : null,
                'nama'            => "X {$kodeJurusan} 1",
                'kode'            => "X-{$kodeJurusan}-1",
                'deskripsi'       => "Kelas X {$komptAhli->nama} 1",
                'kompt_ahli_id'   => $komptAhli->id,
            ];
            $kelasList[] = [
                'tingkat_id'      => $tingkatX ? $tingkatX->id : null,
                'nama'            => "X {$kodeJurusan} 2",
                'kode'            => "X-{$kodeJurusan}-2",
                'deskripsi'       => "Kelas X {$komptAhli->nama} 2",
                'kompt_ahli_id'   => $komptAhli->id,
            ];

            // Kelas XI
            $kelasList[] = [
                'tingkat_id'      => $tingkatXI ? $tingkatXI->id : null,
                'nama'            => "XI {$kodeJurusan} 1",
                'kode'            => "XI-{$kodeJurusan}-1",
                'deskripsi'       => "Kelas XI {$komptAhli->nama} 1",
                'kompt_ahli_id'   => $komptAhli->id,
            ];
            $kelasList[] = [
                'tingkat_id'      => $tingkatXI ? $tingkatXI->id : null,
                'nama'            => "XI {$kodeJurusan} 2",
                'kode'            => "XI-{$kodeJurusan}-2",
                'deskripsi'       => "Kelas XI {$komptAhli->nama} 2",
                'kompt_ahli_id'   => $komptAhli->id,
            ];

            // Kelas XII
            $kelasList[] = [
                'tingkat_id'      => $tingkatXII ? $tingkatXII->id : null,
                'nama'            => "XII {$kodeJurusan} 1",
                'kode'            => "XII-{$kodeJurusan}-1",
                'deskripsi'       => "Kelas XII {$komptAhli->nama} 1",
                'kompt_ahli_id'   => $komptAhli->id,
            ];
            $kelasList[] = [
                'tingkat_id'      => $tingkatXII ? $tingkatXII->id : null,
                'nama'            => "XII {$kodeJurusan} 2",
                'kode'            => "XII-{$kodeJurusan}-2",
                'deskripsi'       => "Kelas XII {$komptAhli->nama} 2",
                'kompt_ahli_id'   => $komptAhli->id,
            ];
        }

        // Simpan kelas dan simpan id-nya
        $kelasMap = [];
        foreach ($kelasList as $kelas) {
            $kelasBaru = Kelas::create($kelas);
            $kelasMap[$kelasBaru->kode] = $kelasBaru;
        }

        // Penempatan siswa ke kelas (bagi rata)
        $allSiswa = Siswa::all();
        $kelasIds = array_values(array_map(function($k) { return $k->id; }, $kelasMap));
        $jumlahKelas = count($kelasIds);
        $now = Carbon::now()->toDateString();

        $i = 0;
        foreach ($allSiswa as $siswa) {
            $kelasId = $kelasIds[$i % $jumlahKelas];

            // Update kelas_id pada tabel siswa
            $siswa->kelas_id = $kelasId;
            $siswa->save();

            // Catat riwayat penempatan siswa di kelas (pakai model SiswaKelas)
            SiswaKelas::create([
                'siswa_id' => $siswa->id,
                'kelas_id' => $kelasId,
                'mulai'    => $now,
                'selesai'  => null,
                'is_aktif' => true,
            ]);
            $i++;
        }

        // Penempatan wali kelas (guru) ke kelas
        $allGuru = Guru::all();
        $kelasListForWali = array_values($kelasMap);
        $guruCount = $allGuru->count();
        $kelasCount = count($kelasListForWali);
        $count = min($guruCount, $kelasCount);

        for ($i = 0; $i < $count; $i++) {
            $kelasObj = $kelasListForWali[$i];
            $guru = $allGuru[$i];

            // Catat riwayat wali kelas (pakai model RiwayatWalikelas)
            RiwayatWalikelas::create([
                'kelas_id' => $kelasObj->id,
                'guru_id'  => $guru->id,
                'mulai'    => $now,
                'selesai'  => null,
                'is_aktif' => true,
            ]);
        }
    }
}
