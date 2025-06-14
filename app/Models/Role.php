<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'identifier',
        'faculty_id',
        'all_permissions',
        'is_active',
        'guard_name'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }

    /**
     * Define the many-to-many relationship with users.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }



    // Add this accessor if you're using identifier field
    public function getIdentifierAttribute()
    {
        return $this->attributes['identifier'] ?? $this->name;
    }
}
