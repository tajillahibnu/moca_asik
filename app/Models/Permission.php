<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'slug',
        'name',
        'guard_name',
        'group',
        'description'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
    }
}