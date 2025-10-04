<?php

namespace Modules\KompetensiKeahlian\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Guru\App\Models\Guru;

class KompetensiKeahlian extends Model
{
    use SoftDeletes;

    protected $table = 'kompetensi_keahlians';

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

    protected $hide = [
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

    // Relasi ke user yang membuat, mengupdate, menghapus disembunyikan/di-nonaktifkan
    // Kolom tanggal (created_at, updated_at, deleted_at) juga disembunyikan
}
