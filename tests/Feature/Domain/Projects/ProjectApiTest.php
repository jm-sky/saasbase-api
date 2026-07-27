<?php

namespace Tests\Feature\Domain\Projects;

use App\Domain\Auth\Models\User;
use App\Domain\Projects\Controllers\ProjectController;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ProjectStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(ProjectController::class)]
class ProjectApiTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/projects';

    private Tenant $tenant;

    private User $user;

    private ProjectStatus $status;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user = $this->authenticateUser($this->tenant);

        Tenant::bypassTenant($this->tenant->id, function () {
            $this->status = ProjectStatus::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });
    }

    public function test_can_list_projects_for_current_tenant_only(): void
    {
        Tenant::bypassTenant($this->tenant->id, function () {
            Project::factory()->count(2)->create([
                'tenant_id' => $this->tenant->id,
                'owner_id' => $this->user->id,
                'status_id' => $this->status->id,
            ]);
        });

        $otherTenant = Tenant::factory()->create();
        Tenant::bypassTenant($otherTenant->id, function () use ($otherTenant) {
            $otherStatus = ProjectStatus::factory()->create([
                'tenant_id' => $otherTenant->id,
            ]);
            Project::factory()->count(3)->create([
                'tenant_id' => $otherTenant->id,
                'status_id' => $otherStatus->id,
            ]);
        });

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_project(): void
    {
        $payload = [
            'name' => 'Test Project',
            'description' => 'Created via Feature test',
            'statusId' => $this->status->id,
            'startDate' => now()->toDateString(),
        ];

        $response = $this->postJson($this->baseUrl, $payload);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonPath('data.name', 'Test Project')
            ->assertJsonPath('data.tenantId', $this->tenant->id);

        $this->assertDatabaseHas('projects', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Project',
            'owner_id' => $this->user->id,
            'status_id' => $this->status->id,
        ]);
    }

    public function test_can_show_project(): void
    {
        $project = Tenant::bypassTenant($this->tenant->id, function () {
            return Project::factory()->create([
                'tenant_id' => $this->tenant->id,
                'owner_id' => $this->user->id,
                'status_id' => $this->status->id,
            ]);
        });

        $response = $this->getJson($this->baseUrl.'/'.$project->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.id', $project->id);
    }

    public function test_owner_can_delete_project(): void
    {
        $project = Tenant::bypassTenant($this->tenant->id, function () {
            return Project::factory()->create([
                'tenant_id' => $this->tenant->id,
                'owner_id' => $this->user->id,
                'status_id' => $this->status->id,
            ]);
        });

        $response = $this->deleteJson($this->baseUrl.'/'.$project->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_non_owner_cannot_delete_project(): void
    {
        $owner = User::factory()->create();
        $project = Tenant::bypassTenant($this->tenant->id, function () use ($owner) {
            return Project::factory()->create([
                'tenant_id' => $this->tenant->id,
                'owner_id' => $owner->id,
                'status_id' => $this->status->id,
            ]);
        });

        $response = $this->deleteJson($this->baseUrl.'/'.$project->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'deleted_at' => null,
        ]);
    }

    public function test_cannot_show_project_from_other_tenant(): void
    {
        $otherTenant = Tenant::factory()->create();
        $project = Tenant::bypassTenant($otherTenant->id, function () use ($otherTenant) {
            $status = ProjectStatus::factory()->create([
                'tenant_id' => $otherTenant->id,
            ]);

            return Project::factory()->create([
                'tenant_id' => $otherTenant->id,
                'status_id' => $status->id,
            ]);
        });

        $response = $this->getJson($this->baseUrl.'/'.$project->id);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
