<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'role',
        'staff_id',
        'phone',
        'address',
        'status',
        'last_login_at',


    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function createdFaculties()
    {
        return $this->hasMany(Faculty::class, 'created_by');
    }

    public function createdDepartments()
    {
        return $this->hasMany(Department::class, 'created_by');
    }

    public function createdLevels()
    {
        return $this->hasMany(Level::class, 'created_by');
    }

    public function createdLogbooks()
    {
        return $this->hasMany(Logbook::class, 'creator_id');
    }

    public function logbookEntries()
    {
        return $this->hasMany(LogbookEntry::class, 'created_by');
    }

    public function revisedEntries()
    {
        return $this->hasMany(LogbookEntry::class, 'revised_by');
    }

    public function attachments()
    {
        return $this->hasMany(LogbookAttachment::class, 'uploaded_by');
    }

    public function createdTemplates()
    {
        return $this->hasMany(LogbookTemplate::class, 'created_by');
    }

    public function assignments()
    {
        return $this->hasMany(UserAssignment::class);
    }
}
