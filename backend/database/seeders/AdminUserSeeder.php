<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $libraryStaffRole = Role::where('name', 'library_staff')->first();
        $facultyRole = Role::where('name', 'faculty')->first();
        $studentRole = Role::where('name', 'student')->first();

        // Create admin user
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@thesisconnect.com',
            'password' => Hash::make('admin123'),
            'role_id' => $adminRole->id,
            'department' => 'IT Department',
            'is_active' => true,
            'is_approved' => true,
        ]);

        // Create library staff user
        User::create([
            'name' => 'Library Staff',
            'email' => 'librarian@thesisconnect.com',
            'password' => Hash::make('librarian123'),
            'role_id' => $libraryStaffRole->id,
            'department' => 'Library',
            'is_active' => true,
            'is_approved' => true,
        ]);

        // Create faculty user
        User::create([
            'name' => 'Dr. Maria Santos',
            'email' => 'faculty@thesisconnect.com',
            'password' => Hash::make('faculty123'),
            'role_id' => $facultyRole->id,
            'department' => 'College of Computer Study',
            'program' => 'Bachelor of Science in Computer Science',
            'faculty_id' => 'FAC-2026-001',
            'is_active' => true,
            'is_approved' => true,
        ]);

        // Create sample student user
        User::create([
            'name' => 'John Doe',
            'email' => 'student@thesisconnect.com',
            'password' => Hash::make('student123'),
            'role_id' => $studentRole->id,
            'department' => 'College of Computer Study',
            'program' => 'Bachelor of Science in Computer Science',
            'student_id' => 'STU-2026-001',
            'is_active' => true,
            'is_approved' => true,
        ]);
    }
}