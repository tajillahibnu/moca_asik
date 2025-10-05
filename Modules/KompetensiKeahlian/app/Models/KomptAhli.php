<?php

namespace Modules\KompetensiKeahlian\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Guru\Models\Guru;

class KomptAhli extends Model
{
    use SoftDeletes;

    protected $table = 'kompt_ahlis';

    protected $fillable = [
        'public_url_code',
        'nama',
        'kode',
        'slug',
        'deskripsi',
        'kepala_jurusan_id',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Relasi ke model Guru sebagai kepala jurusan.
     */
    public function kepalaJurusan()
    {
        return $this->belongsTo(Guru::class, 'kepala_jurusan_id');
    }

    // protected static function newFactory(): KomptAhliFactory
    // {
    //     // return KomptAhliFactory::new();
    // }
}
