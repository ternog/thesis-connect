<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Computer Science',
                'slug' => 'computer-science',
                'description' => 'Computer Science and Information Technology related research'
            ],
            [
                'name' => 'Engineering',
                'slug' => 'engineering',
                'description' => 'Engineering disciplines and applied sciences'
            ],
            [
                'name' => 'Business Administration',
                'slug' => 'business-administration',
                'description' => 'Business, Management, and Entrepreneurship studies'
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'description' => 'Educational research and pedagogy'
            ],
            [
                'name' => 'Health Sciences',
                'slug' => 'health-sciences',
                'description' => 'Medical, nursing, and health-related research'
            ],
            [
                'name' => 'Social Sciences',
                'slug' => 'social-sciences',
                'description' => 'Psychology, sociology, and social work research'
            ],
            [
                'name' => 'Natural Sciences',
                'slug' => 'natural-sciences',
                'description' => 'Biology, chemistry, physics, and environmental sciences'
            ],
            [
                'name' => 'Arts and Humanities',
                'slug' => 'arts-humanities',
                'description' => 'Literature, history, philosophy, and cultural studies'
            ],
            [
                'name' => 'Mathematics',
                'slug' => 'mathematics',
                'description' => 'Pure and applied mathematics research'
            ],
            [
                'name' => 'Agriculture',
                'slug' => 'agriculture',
                'description' => 'Agricultural sciences and food technology'
            ]
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }
    }
}