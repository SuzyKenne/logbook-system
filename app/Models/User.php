<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'staff_id',
        'role_user',
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
            'last_login_at' => 'datetime',
        ];
    }

    // Staff Profile Relationship
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    // Existing relationships
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

    // Helper methods for role and permission management
    public function getPrimaryRole()
    {
        return $this->roles->first();
    }

    public function getPrimaryRoleName()
    {
        return $this->getPrimaryRole()?->name ?? 'No Role';
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('superadmin');
    }

    public function isStudent()
    {
        return $this->hasRole('student');
    }

    public function isLecturer()
    {
        return $this->hasRole('lecturer');
    }

    public function isDean()
    {
        return $this->hasRole('dean');
    }

    public function isHod()
    {
        return $this->hasRole('hod');
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function getAllPermissions()
    {
        return $this->getPermissionsViaRoles()->merge($this->getDirectPermissions());
    }

    // Staff-related helper methods
    public function getStaffProfile()
    {
        return $this->staff;
    }

    public function hasStaffProfile(): bool
    {
        return $this->staff !== null;
    }

    public function getFullName()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    // Get accessible data based on staff role
    public function getAccessibleFaculties()
    {
        if ($this->isAdmin()) {
            return Faculty::all();
        }

        if ($this->staff && $this->staff->isDean()) {
            return Faculty::where('id', $this->staff->faculty_id)->get();
        }

        return collect();
    }

    public function getAccessibleDepartments()
    {
        if ($this->isAdmin()) {
            return Department::all();
        }

        if ($this->staff && $this->staff->isDean()) {
            return Department::where('faculty_id', $this->staff->faculty_id)->get();
        }

        if ($this->staff && $this->staff->isHod()) {
            return Department::where('id', $this->staff->department_id)->get();
        }

        return collect();
    }

    public function getAccessibleCourses()
    {
        if ($this->isAdmin()) {
            return Course::all();
        }

        if ($this->staff) {
            if ($this->staff->isDean()) {
                return Course::whereHas('department', function ($query) {
                    $query->where('faculty_id', $this->staff->faculty_id);
                });
            }

            if ($this->staff->isHod()) {
                return Course::where('department_id', $this->staff->department_id);
            }

            if ($this->staff->isLecturer()) {
                return $this->staff->courses();
            }
        }

        return collect();
    }
}
