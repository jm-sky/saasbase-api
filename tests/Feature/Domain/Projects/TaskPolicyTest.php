<?php

namespace Tests\Feature\Domain\Projects;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ProjectStatus;
use App\Domain\Projects\Models\Task;
use App\Domain\Projects\Models\TaskStatus;
use App\Domain\Projects\Policies\TaskPolicy;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * ProjectPolicy delete boundaries are covered in ProjectApiTest.
 * This file locks TaskPolicy view/delete gates.
 *
 * @internal
 */
#[CoversClass(TaskPolicy::class)]
class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private Tenant $tenant;

    private User $owner;

    private Project $project;

    private TaskStatus $taskStatus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->owner = $this->authenticateUser($this->tenant);

        Tenant::bypassTenant($this->tenant->id, function () {
            $projectStatus = ProjectStatus::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
            $this->project = Project::factory()->create([
                'tenant_id' => $this->tenant->id,
                'owner_id' => $this->owner->id,
                'status_id' => $projectStatus->id,
            ]);
            $this->taskStatus = TaskStatus::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });
    }

    public function test_creator_can_view_and_delete_task(): void
    {
        $task = $this->createTask(createdById: $this->owner->id);

        $this->getJson('/api/v1/tasks/'.$task->id)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.id', $task->id);

        $this->deleteJson('/api/v1/tasks/'.$task->id)
            ->assertStatus(Response::HTTP_NO_CONTENT);

        // Task model does not use SoftDeletes yet (column exists, trait missing).
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_unrelated_tenant_member_cannot_view_or_delete_task(): void
    {
        $task = $this->createTask(createdById: $this->owner->id);

        $stranger = User::factory()->create();
        $stranger->tenants()->attach($this->tenant, ['role' => RoleName::User->value]);
        TenantScopedRoles::assign($stranger, RoleName::User->value, $this->tenant->id);
        $this->withHeader('Authorization', 'Bearer '.JwtHelper::createTokenWithTenant($stranger, $this->tenant->id));

        $this->getJson('/api/v1/tasks/'.$task->id)
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->deleteJson('/api/v1/tasks/'.$task->id)
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'deleted_at' => null,
        ]);
    }

    private function createTask(string $createdById): Task
    {
        return Tenant::bypassTenant($this->tenant->id, function () use ($createdById) {
            return Task::create([
                'tenant_id' => $this->tenant->id,
                'project_id' => $this->project->id,
                'status_id' => $this->taskStatus->id,
                'title' => 'Policy gate task',
                'priority' => 'medium',
                'assignee_id' => $createdById,
                'created_by_id' => $createdById,
            ]);
        });
    }
}
