<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LogbookAttachment extends Model
{
    
    use HasFactory;

    protected $fillable = [
        'logbook_entry_id',
        'uploaded_by',
        'original_name',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'description',
        'attachment_type',
    ];


    // Relationships
    public function logbookEntry()
    {
        return $this->belongsTo(LogbookEntry::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
