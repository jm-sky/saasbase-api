<?php

namespace Tests\Unit\Domain\Projects;

use App\Domain\Projects\DTOs\ProjectDTO;
use App\Domain\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDTOTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_project_dto_from_model(): void
    {
        $project = Project::factory()->create();
        $dto = ProjectDTO::fromModel($project);

        $this->assertEquals($project->id, $dto->id);
        $this->assertEquals($project->tenant_id, $dto->tenantId);
        $this->assertEquals($project->name, $dto->name);
        $this->assertEquals($project->description, $dto->description);
        $this->assertEquals($project->status, $dto->status);
        $this->assertEquals($project->start_date?->toDateString(), $dto->startDate);
        $this->assertEquals($project->end_date?->toDateString(), $dto->endDate);
        $this->assertEquals($project->created_at?->toIso8601String(), $dto->createdAt?->toIso8601String());
        $this->assertEquals($project->updated_at?->toIso8601String(), $dto->updatedAt?->toIso8601String());
        $this->assertEquals($project->deleted_at?->toIso8601String(), $dto->deletedAt?->toIso8601String());
        $this->assertNull($dto->owner);
        $this->assertNull($dto->tasks);
        $this->assertNull($dto->requiredSkills);
    }

    public function test_can_create_project_dto_with_relations(): void
    {
        $project = Project::factory()->create();
        $project->load(['owner', 'tasks', 'requiredSkills']);

        $dto = ProjectDTO::fromModel($project, true);

        $this->assertNotNull($dto->owner);
        $this->assertNotNull($dto->tasks);
        $this->assertNotNull($dto->requiredSkills);
    }

    public function test_can_convert_project_dto_to_array(): void
    {
        $project = Project::factory()->create();
        $dto = ProjectDTO::fromModel($project);
        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals($project->id, $array['id']);
        $this->assertEquals($project->tenant_id, $array['tenantId']);
        $this->assertEquals($project->name, $array['name']);
        $this->assertEquals($project->description, $array['description']);
        $this->assertEquals($project->status, $array['status']);
        $this->assertEquals($project->start_date?->toDateString(), $array['startDate']);
        $this->assertEquals($project->end_date?->toDateString(), $array['endDate']);
        $this->assertEquals($project->created_at?->toIso8601String(), $array['createdAt']);
        $this->assertEquals($project->updated_at?->toIso8601String(), $array['updatedAt']);
        $this->assertEquals($project->deleted_at?->to, $array['deletedAt']);
        $this->assertNull($array['owner']);
        $this->assertNull($array['tasks']);
        $this->assertNull($array['required_skills']);
    }

    public function test_can_convert_project_dto_with_relations_to_array(): void
    {
        $project = Project::factory()->create();
        $project->load(['owner', 'tasks', 'requiredSkills']);

        $dto = ProjectDTO::fromModel($project, true);
        $array = $dto->toArray();

        $this->assertIsArray($array['owner']);
        $this->assertIsArray($array['tasks']);
        $this->assertIsArray($array['required_skills']);
    }
}
