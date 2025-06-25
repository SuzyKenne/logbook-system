<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'level_id',
        'creator_id',
        'title',
        'description',
        'course_code',
        'course_name',
        'logbook_type',
        'start_date',
        'end_date',
        'total_sessions',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function entries()
    {
        return $this->hasMany(LogbookEntry::class);
    }

    public function getEncodedAttribute($key)
    {
        $value = $this->attributes[$key] ?? '';
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
}
