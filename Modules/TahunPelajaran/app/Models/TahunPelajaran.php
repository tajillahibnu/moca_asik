<?php

namespace Modules\TahunPelajaran\Models;

use Illuminate\Database\Eloquent\Model;

class TahunPelajaran extends Model
{
    protected $table = 'tahun_pelajarans';

    protected $fillable = [
        'nama',
        'is_aktif',
    ];
}
