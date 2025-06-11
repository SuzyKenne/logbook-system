<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'staff_id' => 'ADM001',
                'phone' => '+237600000001',
                'address' => 'Buea, South-West Region',
                'status' => 'active',
                'last_login_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'name' => 'Jane Smith',
                'email' => 'instructor@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'lecturer',
                'staff_id' => 'INS001',
                'phone' => '+237600000002',
                'address' => 'Limbe, South-West Region',
                'status' => 'active',
                'last_login_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Student',
                'name' => 'John Student',
                'email' => 'student@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'staff_id' => 'STU001',
                'phone' => '+237600000003',
                'address' => 'Bamenda, North-West Region',
                'status' => 'active',
                'last_login_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
