<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Staff extends Model
{
    use HasFactory;

    // Add this line to specify the correct table name
    protected $table = 'staffs';

    protected $fillable = [
        'user_id',
        'faculty_id',
        'department_id',
        'staff_type', // 'dean', 'hod', 'lecturer'
        'staff_id',
        'designation',
        'qualification',
        'specialization',
        'hire_date',
        'status', // 'active', 'inactive', 'suspended'
        'office_location',
        'office_phone',
        'bio',
        'is_head_of_department',
        'is_dean',
    ];

    protected $casts = [
        'is_head_of_department' => 'boolean',
        'is_dean' => 'boolean',
        'hire_date' => 'date', // Add this cast since you're using hire_date in your Filament form
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_staff')
            ->withPivot(['semester', 'academic_year', 'is_coordinator'])
            ->withTimestamps();
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class, 'assigned_staff_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDeans($query)
    {
        return $query->where('is_dean', true);
    }

    public function scopeHods($query)
    {
        return $query->where('is_head_of_department', true);
    }

    public function scopeLecturers($query)
    {
        return $query->where('staff_type', 'lecturer');
    }

    public function scopeByFaculty($query, $facultyId)
    {
        return $query->where('faculty_id', $facultyId);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Helper Methods
    public function getFullNameAttribute()
    {
        return $this->user->first_name . ' ' . $this->user->last_name;
    }

    public function isDean(): bool
    {
        return $this->is_dean || $this->staff_type === 'dean';
    }

    public function isHod(): bool
    {
        return $this->is_head_of_department || $this->staff_type === 'hod';
    }

    public function isLecturer(): bool
    {
        return $this->staff_type === 'lecturer';
    }

    public function canManageFaculty(): bool
    {
        return $this->isDean() || $this->user->isAdmin();
    }

    public function canManageDepartment(): bool
    {
        return $this->isHod() || $this->isDean() || $this->user->isAdmin();
    }

    public function getAssignedCourses()
    {
        return $this->courses()->wherePivot('semester', now()->format('Y-m'));
    }

    public function getLogbooksInCurrentSemester()
    {
        return $this->logbooks()->whereHas('course', function ($query) {
            $query->where('semester', now()->format('Y-m'));
        });
    }

    // Get staff members under this person's authority
    public function getSubordinateStaff()
    {
        if ($this->isDean()) {
            // Dean can see all staff in their faculty
            return Staff::where('faculty_id', $this->faculty_id)
                ->where('id', '!=', $this->id);
        }

        if ($this->isHod()) {
            // HOD can see all lecturers in their department
            return Staff::where('department_id', $this->department_id)
                ->where('staff_type', 'lecturer');
        }

        return collect(); // Lecturers have no subordinates
    }

    // Get accessible logbooks based on role
    public function getAccessibleLogbooks()
    {
        if ($this->user->isAdmin()) {
            return Logbook::all();
        }

        if ($this->isDean()) {
            return Logbook::whereHas('course.department', function ($query) {
                $query->where('faculty_id', $this->faculty_id);
            });
        }

        if ($this->isHod()) {
            return Logbook::whereHas('course', function ($query) {
                $query->where('department_id', $this->department_id);
            });
        }

        // Lecturers can only see their own logbooks
        return $this->logbooks();
    }
}
