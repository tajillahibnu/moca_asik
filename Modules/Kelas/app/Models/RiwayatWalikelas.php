<?php

namespace Modules\Kelas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Guru\Models\Guru;

// use Modules\Kelas\Database\Factories\RiwayatWalikelasFactory;

class RiwayatWalikelas extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'kelas_id',
        'guru_id',
        'mulai',
        'selesai',
        'is_aktif',
    ];

    /**
     * Get the kelas associated with the riwayat walikelas.
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Get the guru associated with the riwayat walikelas.
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    // protected static function newFactory(): RiwayatWalikelasFactory
    // {
    //     // return RiwayatWalikelasFactory::new();
    // }
}
