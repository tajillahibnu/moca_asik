<?php

namespace Modules\Siswa\Traits;

trait NisSiswaTrait
{
    /**
     * Generate NIS Siswa dengan format: NPSN.no_urut.tahun
     *
     * @param int|string $noUrut
     * @param int|string|null $tahun
     * @param string|null $npsn
     * @return string
     */
    public function generateNis($noUrut, $tahun = null, $npsn = null)
    {
        // Ambil NPSN dari parameter, config, atau env
        if (empty($npsn)) {
            $npsn = config('sekolah.npsn', env('NPSN', '00000000'));
        }

        // Default tahun ke tahun sekarang jika tidak diberikan
        if (empty($tahun)) {
            $tahun = date('Y');
        }

        // Format nomor urut menjadi 4 digit (misal: 0001)
        $noUrutFormatted = str_pad($noUrut, 4, '0', STR_PAD_LEFT);

        // Format tahun menjadi 4 digit
        $tahunFormatted = str_pad($tahun, 4, '0', STR_PAD_LEFT);

        // Gabungkan menjadi NIS
        return "{$npsn}.{$noUrutFormatted}.{$tahunFormatted}";
    }
}
