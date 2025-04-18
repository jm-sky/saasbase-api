<?php

namespace Database\Seeders;

use App\Domain\Projects\Models\ProjectRole;
use Illuminate\Database\Seeder;

class ProjectRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Project Manager',
                'description' => 'Manages and oversees the entire project',
                'permissions' => [
                    'project.view',
                    'project.edit',
                    'project.delete',
                    'task.create',
                    'task.edit',
                    'task.delete',
                    'member.add',
                    'member.remove',
                    'role.assign',
                ],
            ],
            [
                'name' => 'Team Lead',
                'description' => 'Leads a team within the project',
                'permissions' => [
                    'project.view',
                    'task.create',
                    'task.edit',
                    'task.delete',
                    'member.add',
                ],
            ],
            [
                'name' => 'Developer',
                'description' => 'Works on project tasks',
                'permissions' => [
                    'project.view',
                    'task.view',
                    'task.edit',
                ],
            ],
            [
                'name' => 'Observer',
                'description' => 'Can only view project progress',
                'permissions' => [
                    'project.view',
                    'task.view',
                ],
            ],
        ];

        foreach ($roles as $role) {
            ProjectRole::create($role);
        }
    }
}
