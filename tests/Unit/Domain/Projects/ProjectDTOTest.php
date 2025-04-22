<?php

namespace Tests\Unit\Domain\Projects;

use App\Domain\Projects\DTOs\ProjectDTO;
use App\Domain\Projects\Models\Project;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;
use Carbon\Carbon;

/**
 * @internal
 *
 * @coversNothing
 */
class ProjectDTOTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = $this->authenticateUser($this->tenant);
    }

    public function testFromModel(): void
    {
        $project = Project::withoutTenantScope(function () {
            return Project::factory()->create([
                'tenant_id' => $this->tenant->id,
                'name'      => 'Test Project',
                'status'    => 'active',
                'start_date' => now(),
            ]);
        });

        $dto = ProjectDTO::from($project);

        $this->assertEquals($project->id, $dto->id);
        $this->assertEquals($project->name, $dto->name);
        $this->assertEquals($project->status, $dto->status);
        $this->assertEquals($project->start_date->format('Y-m-d'), $dto->startDate);
        $this->assertEquals($project->created_at, $dto->createdAt);
        $this->assertEquals($project->updated_at, $dto->updatedAt);
    }

    public function testToModel(): void
    {
        $dto = new ProjectDTO(
            tenantId: $this->tenant->id,
            name: 'Test Project',
            status: 'active',
            startDate: now()->format('Y-m-d'),
            id: null,
        );

        $project = Project::withoutTenantScope(function () use ($dto) {
            return Project::factory()->create([
                'tenant_id' => $dto->tenantId,
                'name' => $dto->name,
                'status' => $dto->status,
                'start_date' => Carbon::parse($dto->startDate),
                'owner_id' => $this->user->id,
            ]);
        });

        $this->assertEquals($dto->name, $project->name);
        $this->assertEquals($dto->status, $project->status);
        $this->assertEquals($dto->startDate, $project->start_date->format('Y-m-d'));
    }

    public function testFromCollection(): void
    {
        $projects = collect([
            Project::withoutTenantScope(function () {
                return Project::factory()->create([
                    'tenant_id' => $this->tenant->id,
                    'name'      => 'Test Project 1',
                    'status'    => 'active',
                    'start_date' => now(),
                ]);
            }),
            Project::withoutTenantScope(function () {
                return Project::factory()->create([
                    'tenant_id' => $this->tenant->id,
                    'name'      => 'Test Project 2',
                    'status'    => 'completed',
                    'start_date' => now(),
                ]);
            }),
        ]);

        $dtos = ProjectDTO::collect($projects);

        $this->assertCount(2, $dtos);
        $this->assertEquals('Test Project 1', $dtos[0]->name);
        $this->assertEquals('Test Project 2', $dtos[1]->name);
    }

    public function testToArray(): void
    {
        $project = Project::withoutTenantScope(function () {
            return Project::factory()->create([
                'tenant_id' => $this->tenant->id,
                'name'      => 'Test Project',
                'status'    => 'active',
                'start_date' => now(),
            ]);
        });

        $dto = ProjectDTO::from($project);
        $array = $dto->toArray();

        $this->assertEquals($project->id, $array['id']);
        $this->assertEquals($project->name, $array['name']);
        $this->assertEquals($project->status, $array['status']);
        $this->assertEquals($project->start_date->format('Y-m-d'), $array['startDate']);
        $this->assertEquals($project->created_at->toIso8601String(), $array['createdAt']);
        $this->assertEquals($project->updated_at->toIso8601String(), $array['updatedAt']);
    }
}
