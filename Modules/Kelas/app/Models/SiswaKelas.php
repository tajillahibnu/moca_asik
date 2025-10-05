<?php

namespace Modules\Kelas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Siswa\Models\Siswa;

// use Modules\Kelas\Database\Factories\SiswaKelasFactory;

class SiswaKelas extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'mulai',
        'selesai',
        'is_aktif',
    ];

    /**
     * Get the siswa associated with this SiswaKelas.
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Get the kelas associated with this SiswaKelas.
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    // protected static function newFactory(): SiswaKelasFactory
    // {
    //     // return SiswaKelasFactory::new();
    // }
}
