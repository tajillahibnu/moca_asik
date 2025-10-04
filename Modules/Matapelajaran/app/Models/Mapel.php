<?php

namespace Modules\Matapelajaran\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mapel extends Model
{
    protected $table = 'mapels';

    protected $fillable = [
        'kode',
        'nama',
        'nama_report',
        'parent_id',
        'is_aktif',
    ];

    /**
     * Relasi ke mapel induk (parent).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Relasi ke mapel anak (children).
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
