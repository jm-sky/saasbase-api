<?php

namespace Tests\Unit\Domain\Projects;

use App\Domain\Projects\DTOs\ProjectDto;
use App\Domain\Projects\Models\Project;
use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Tests\TestCase;

class ProjectDtoTest extends TestCase
{
    public function test_can_create_project_dto_from_model(): void
    {
        $project = Project::factory()->create();
        $dto = ProjectDto::fromModel($project);

        $this->assertEquals($project->id, $dto->id);
        $this->assertEquals($project->tenant_id, $dto->tenant_id);
        $this->assertEquals($project->name, $dto->name);
        $this->assertEquals($project->description, $dto->description);
        $this->assertEquals($project->status, $dto->status);
        $this->assertEquals($project->start_date, $dto->start_date);
        $this->assertEquals($project->end_date, $dto->end_date);
        $this->assertEquals($project->created_at, $dto->created_at);
        $this->assertEquals($project->updated_at, $dto->updated_at);
        $this->assertEquals($project->deleted_at, $dto->deleted_at);
        $this->assertNull($dto->owner);
        $this->assertNull($dto->users);
        $this->assertNull($dto->tasks);
        $this->assertNull($dto->requiredSkills);
    }

    public function test_can_create_project_dto_with_relations(): void
    {
        $project = Project::factory()->create();
        $project->load(['owner', 'users', 'tasks', 'requiredSkills']);

        $dto = ProjectDto::fromModel($project, true);

        $this->assertNotNull($dto->owner);
        $this->assertNotNull($dto->users);
        $this->assertNotNull($dto->tasks);
        $this->assertNotNull($dto->requiredSkills);
    }

    public function test_can_convert_project_dto_to_array(): void
    {
        $project = Project::factory()->create();
        $dto = ProjectDto::fromModel($project);
        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals($project->id, $array['id']);
        $this->assertEquals($project->tenant_id, $array['tenant_id']);
        $this->assertEquals($project->name, $array['name']);
        $this->assertEquals($project->description, $array['description']);
        $this->assertEquals($project->status, $array['status']);
        $this->assertEquals($project->start_date, $array['start_date']);
        $this->assertEquals($project->end_date, $array['end_date']);
        $this->assertEquals($project->created_at, $array['created_at']);
        $this->assertEquals($project->updated_at, $array['updated_at']);
        $this->assertEquals($project->deleted_at, $array['deleted_at']);
        $this->assertNull($array['owner']);
        $this->assertNull($array['users']);
        $this->assertNull($array['tasks']);
        $this->assertNull($array['required_skills']);
    }

    public function test_can_convert_project_dto_with_relations_to_array(): void
    {
        $project = Project::factory()->create();
        $project->load(['owner', 'users', 'tasks', 'requiredSkills']);

        $dto = ProjectDto::fromModel($project, true);
        $array = $dto->toArray();

        $this->assertIsArray($array['owner']);
        $this->assertIsArray($array['users']);
        $this->assertIsArray($array['tasks']);
        $this->assertIsArray($array['required_skills']);
    }
}
