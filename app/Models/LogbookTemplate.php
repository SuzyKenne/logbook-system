<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogbookTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'field_configuration',
        'template_type',
        'is_default',
        'status',
        'created_by',
    ];

    protected $casts = [
        'field_configuration' => 'array',
        'is_default' => 'boolean',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
