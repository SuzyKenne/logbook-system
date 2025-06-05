<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'code',
        'academic_year',
        'semester',
        'description',
        'status',
        'created_by',
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logbooks()
    {
        return $this->hasMany(Logbook::class);
    }
}
