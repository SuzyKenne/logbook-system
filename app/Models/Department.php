<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'name',
        'code',
        'description',
        'head_name',
        'head_email',
        'location',
        'status',
        'created_by',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function levels()
    {
        return $this->hasMany(Level::class);
    }

    public function logbooks()
    {
        return $this->hasMany(Logbook::class);
    }

    public function userAssignments()
    {
        return $this->hasMany(UserAssignment::class);
    }
}
