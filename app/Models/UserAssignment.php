<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAssignment extends Model
{

    use HasFactory;

    protected $fillable = [
        'user_id',
        'faculty_id',
        'department_id',
        'assignment_type',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
