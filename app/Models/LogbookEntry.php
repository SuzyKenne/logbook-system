<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogbookEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'logbook_id',
        'created_by',
        'entry_date',
        'start_time',
        'end_time',
        'session_title',
        'content',
        'objectives',
        'activities',
        'outcomes',
        'assignments',
        'remarks',
        'status',
        'revised_by',
        'revised_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'revised_at' => 'datetime',
    ];

    // Relationships
    public function logbook()
    {
        return $this->belongsTo(Logbook::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function revisor()
    {
        return $this->belongsTo(User::class, 'revised_by');
    }

    public function attachments()
    {
        return $this->hasMany(LogbookAttachment::class);
    }
    public function getEncodedAttribute($key)
    {
        $value = $this->attributes[$key] ?? '';
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
}
