<?php

namespace Database\Seeders;

use App\Domain\Skills\Models\SkillCategory;
use Illuminate\Database\Seeder;

class SkillCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Programming Languages',
                'description' => 'Programming and scripting languages',
            ],
            [
                'name'        => 'Web Development',
                'description' => 'Web development technologies and frameworks',
            ],
            [
                'name'        => 'Mobile Development',
                'description' => 'Mobile app development platforms and frameworks',
            ],
            [
                'name'        => 'Database',
                'description' => 'Database management systems and related technologies',
            ],
            [
                'name'        => 'DevOps',
                'description' => 'Development operations, CI/CD, and infrastructure',
            ],
            [
                'name'        => 'Cloud Computing',
                'description' => 'Cloud platforms and services',
            ],
            [
                'name'        => 'Security',
                'description' => 'Cybersecurity and information security',
            ],
            [
                'name'        => 'AI & Machine Learning',
                'description' => 'Artificial Intelligence and Machine Learning technologies',
            ],
        ];

        foreach ($categories as $category) {
            SkillCategory::create($category);
        }
    }
}
