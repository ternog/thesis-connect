<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access and management',
                'permissions' => [
                    'manage_users',
                    'manage_roles',
                    'upload_thesis',
                    'approve_thesis',
                    'delete_thesis',
                    'manage_categories',
                    'view_reports',
                    'manage_system'
                ]
            ],
            [
                'name' => 'library_staff',
                'display_name' => 'Library Staff',
                'description' => 'Library management and thesis approval',
                'permissions' => [
                    'upload_thesis',
                    'approve_thesis',
                    'edit_thesis_metadata',
                    'manage_categories',
                    'view_reports',
                    'archive_thesis'
                ]
            ],
            [
                'name' => 'faculty',
                'display_name' => 'Faculty',
                'description' => 'Upload own research and thesis',
                'permissions' => [
                    'upload_own_thesis',
                    'edit_own_thesis'
                ]
            ],
            [
                'name' => 'researcher',
                'display_name' => 'Researcher',
                'description' => 'Search and download authorized documents',
                'permissions' => [
                    'search_thesis',
                    'view_thesis',
                    'download_authorized'
                ]
            ],
            [
                'name' => 'student',
                'display_name' => 'Student',
                'description' => 'Search and view thesis documents',
                'permissions' => [
                    'search_thesis',
                    'view_thesis',
                    'download_authorized'
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }
    }
}