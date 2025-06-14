<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Staff;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $roles = ['admin', 'dean', 'hod', 'lecturer'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $allPermissions = Permission::where('guard_name', 'web')->pluck('id');
        $role = Role::where('id', 1)->first();
        $role->syncPermissions($allPermissions);


        $user = User::first();
        if ($user) {
            $user->assignRole($role);
        }


        // Assuming you have faculties and departments already created
        $faculty = Faculty::first();
        $department = Department::first();

        // Create Admin User
        $adminUser = User::firstOrCreate([
            'email' => 'admin@university.edu'
        ], [
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'name' => 'System Administrator',
            'staff_id' => 'ADMIN001',
            'phone' => '+1234567890',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);
        $adminUser->assignRole('admin');

        // Create Dean User
        if ($faculty) {
            $deanUser = User::firstOrCreate([
                'email' => 'dean@university.edu'
            ], [
                'first_name' => 'John',
                'last_name' => 'Dean',
                'name' => 'John Dean',
                'staff_id' => 'DEAN001',
                'phone' => '+1234567891',
                'status' => 'active',
                'password' => bcrypt('password'),
            ]);
            $deanUser->assignRole('dean');

            Staff::firstOrCreate([
                'user_id' => $deanUser->id
            ], [
                'faculty_id' => $faculty->id,
                'staff_type' => 'dean',
                'employee_id' => 'EMP_DEAN_001',
                'designation' => 'Dean of Faculty',
                'qualification' => 'PhD',
                'hire_date' => now()->subYears(5),
                'status' => 'active',
                'is_dean' => true,
            ]);
        }

        // Create HOD User
        if ($department) {
            $hodUser = User::firstOrCreate([
                'email' => 'hod@university.edu'
            ], [
                'first_name' => 'Jane',
                'last_name' => 'HOD',
                'name' => 'Jane HOD',
                'staff_id' => 'HOD001',
                'phone' => '+1234567892',
                'status' => 'active',
                'password' => bcrypt('password'),
            ]);
            $hodUser->assignRole('hod');

            Staff::firstOrCreate([
                'user_id' => $hodUser->id
            ], [
                'faculty_id' => $department->faculty_id,
                'department_id' => $department->id,
                'staff_type' => 'hod',
                'employee_id' => 'EMP_HOD_001',
                'designation' => 'Head of Department',
                'qualification' => 'PhD',
                'hire_date' => now()->subYears(3),
                'status' => 'active',
                'is_head_of_department' => true,
            ]);
        }

        // Create Lecturer Users
        for ($i = 1; $i <= 3; $i++) {
            $lecturerUser = User::firstOrCreate([
                'email' => "lecturer{$i}@university.edu"
            ], [
                'first_name' => "Lecturer",
                'last_name' => "User {$i}",
                'name' => "Lecturer User {$i}",
                'staff_id' => "LEC00{$i}",
                'phone' => "+123456789{$i}",
                'status' => 'active',
                'password' => bcrypt('password'),
            ]);
            $lecturerUser->assignRole('lecturer');

            if ($department) {
                Staff::firstOrCreate([
                    'user_id' => $lecturerUser->id
                ], [
                    'faculty_id' => $department->faculty_id,
                    'department_id' => $department->id,
                    'staff_type' => 'lecturer',
                    'employee_id' => "EMP_LEC_00{$i}",
                    'designation' => 'Lecturer',
                    'qualification' => $i === 1 ? 'PhD' : 'MSc',
                    'hire_date' => now()->subYears(rand(1, 5)),
                    'status' => 'active',
                ]);
            }
        }
    }
}
