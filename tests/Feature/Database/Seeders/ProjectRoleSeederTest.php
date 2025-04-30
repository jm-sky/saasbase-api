<?php

namespace Tests\Feature\Database\Seeders;

use App\Domain\Projects\Models\ProjectRole;
use Database\Seeders\ProjectRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversNothing]
class ProjectRoleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function testProjectRoleSeederCreatesExpectedRecords(): void
    {
        $this->seed(ProjectRoleSeeder::class);

        $this->assertDatabaseCount('project_roles', 4);

        // Test Project Manager role
        $this->assertDatabaseHas('project_roles', [
            'name'        => 'Project Manager',
            'description' => 'Manages and oversees the entire project',
        ]);

        $projectManager = ProjectRole::where('name', 'Project Manager')->first();
        $this->assertNotNull($projectManager);
        $this->assertContains('project.edit', $projectManager->permissions);
        $this->assertContains('role.assign', $projectManager->permissions);

        // Test Developer role
        $this->assertDatabaseHas('project_roles', [
            'name'        => 'Developer',
            'description' => 'Works on project tasks',
        ]);

        $developer = ProjectRole::where('name', 'Developer')->first();
        $this->assertNotNull($developer);
        $this->assertContains('project.view', $developer->permissions);
        $this->assertContains('task.edit', $developer->permissions);
        $this->assertNotContains('project.delete', $developer->permissions);

        // Test Observer role
        $this->assertDatabaseHas('project_roles', [
            'name'        => 'Observer',
            'description' => 'Can only view project progress',
        ]);

        $observer = ProjectRole::where('name', 'Observer')->first();
        $this->assertNotNull($observer);
        $this->assertContains('project.view', $observer->permissions);
        $this->assertNotContains('task.edit', $observer->permissions);
    }
}
