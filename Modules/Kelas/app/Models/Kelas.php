<?php

namespace Modules\Kelas\Models;

use App\Models\Tingkat;
use Illuminate\Database\Eloquent\Model;
use Modules\KompetensiKeahlian\Models\KomptAhli;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'tingkat_id',
        'nama',
        'kode',
        'deskripsi',
        'kompt_ahli_id',
    ];

    /**
     * Relasi ke model Tingkat
     */
    public function tingkat()
    {
        return $this->belongsTo(Tingkat::class, 'tingkat_id');
    }

    /**
     * Relasi ke model KomptAhli (jika ada)
     */
    public function komptAhli()
    {
        return $this->belongsTo(KomptAhli::class, 'kompt_ahli_id');
    }
}
