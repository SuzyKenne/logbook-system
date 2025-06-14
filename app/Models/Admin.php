<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\AdminFactory> */
    use HasFactory, HasRoles;

    protected $guard = 'superadmin';
    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'password',
        'status',
    ];
}
