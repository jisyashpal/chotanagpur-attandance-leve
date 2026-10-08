<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'College Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'is_profile_complete' => true,
        ]);

        // Student User
        User::create([
            'name' => 'John Student',
            'email' => 'student@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'student',
        ]);

        // Faculty User
        User::create([
            'name' => 'Dr. Faculty',
            'email' => 'faculty@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'faculty',
        ]);

        // Staff User
        User::create([
            'name' => 'Staff Member',
            'email' => 'staff@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'staff',
        ]);
    }
}
