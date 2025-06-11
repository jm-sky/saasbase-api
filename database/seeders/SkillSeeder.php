<?php

namespace Database\Seeders;

use App\Domain\Skills\Models\Skill;
use App\Helpers\Ulid;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            // Programming Languages
            [
                'category'    => 'Programming Languages',
                'name'        => 'PHP',
                'description' => 'PHP programming language',
            ],
            [
                'category'    => 'Programming Languages',
                'name'        => 'JavaScript',
                'description' => 'JavaScript programming language',
            ],
            [
                'category'    => 'Programming Languages',
                'name'        => 'Python',
                'description' => 'Python programming language',
            ],
            [
                'category'    => 'Programming Languages',
                'name'        => 'Java',
                'description' => 'Java programming language',
            ],

            // Web Development
            [
                'category'    => 'Web Development',
                'name'        => 'Laravel',
                'description' => 'PHP web application framework',
            ],
            [
                'category'    => 'Web Development',
                'name'        => 'React',
                'description' => 'JavaScript library for building user interfaces',
            ],
            [
                'category'    => 'Web Development',
                'name'        => 'Vue.js',
                'description' => 'Progressive JavaScript framework',
            ],
            [
                'category'    => 'Web Development',
                'name'        => 'Angular',
                'description' => 'TypeScript-based web application framework',
            ],

            // Mobile Development
            [
                'category'    => 'Mobile Development',
                'name'        => 'React Native',
                'description' => 'Mobile application framework',
            ],
            [
                'category'    => 'Mobile Development',
                'name'        => 'Flutter',
                'description' => 'UI toolkit for building natively compiled applications',
            ],

            // Database
            [
                'category'    => 'Database',
                'name'        => 'MySQL',
                'description' => 'Open-source relational database management system',
            ],
            [
                'category'    => 'Database',
                'name'        => 'PostgreSQL',
                'description' => 'Open-source object-relational database system',
            ],
            [
                'category'    => 'Database',
                'name'        => 'MongoDB',
                'description' => 'NoSQL database program',
            ],

            // DevOps
            [
                'category'    => 'DevOps',
                'name'        => 'Docker',
                'description' => 'Platform for developing, shipping, and running applications',
            ],
            [
                'category'    => 'DevOps',
                'name'        => 'Kubernetes',
                'description' => 'Container orchestration system',
            ],
            [
                'category'    => 'DevOps',
                'name'        => 'Jenkins',
                'description' => 'Open source automation server',
            ],

            // Cloud Computing
            [
                'category'    => 'Cloud Computing',
                'name'        => 'AWS',
                'description' => 'Amazon Web Services cloud platform',
            ],
            [
                'category'    => 'Cloud Computing',
                'name'        => 'Azure',
                'description' => 'Microsoft cloud computing service',
            ],
            [
                'category'    => 'Cloud Computing',
                'name'        => 'Google Cloud',
                'description' => 'Google cloud computing services',
            ],

            // Security
            [
                'category'    => 'Security',
                'name'        => 'Penetration Testing',
                'description' => 'Security testing methodology',
            ],
            [
                'category'    => 'Security',
                'name'        => 'Cryptography',
                'description' => 'Security through encryption',
            ],

            // AI & Machine Learning
            [
                'category'    => 'AI & Machine Learning',
                'name'        => 'TensorFlow',
                'description' => 'Open-source machine learning framework',
            ],
            [
                'category'    => 'AI & Machine Learning',
                'name'        => 'PyTorch',
                'description' => 'Machine learning library',
            ],
        ];

        foreach ($skills as $skill) {
            Skill::create([
                'id' => Ulid::deterministic(['skill', $skill['name']]),
                ...$skill,
            ]);
        }
    }
}
