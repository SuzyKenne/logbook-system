<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

use Illuminate\Database\Eloquent\Model;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'identifier',
        'parent_id',
        'status',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
