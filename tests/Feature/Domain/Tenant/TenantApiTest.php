<?php

namespace Tests\Feature\Domain\Tenant;

use App\Domain\Auth\Models\User;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Tenant\Controllers\TenantController;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(TenantController::class)]
class TenantApiTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/tenants';

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user = $this->authenticateUser($this->tenant);
    }

    public function test_can_list_tenants(): void
    {
        // Clear any existing tenants to ensure clean state
        Tenant::query()->forceDelete();

        $tenants = Tenant::factory()->count(3)->create();
        $this->user->tenants()->attach($tenants, ['role' => RoleName::Admin->value]);

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'createdAt',
                        'updatedAt',
                        'deletedAt',
                    ],
                ],
            ]);

        // Verify we got exactly the tenants we created
        $responseIds = collect($response->json('data'))->pluck('id')->sort()->values();
        $expectedIds = $tenants->pluck('id')->sort()->values();
        $this->assertEquals($expectedIds, $responseIds);
    }

    public function test_can_create_tenant(): void
    {
        $tenantData = [
            'tenant' => [
                'name' => 'Test Tenant',
                'slug' => 'test-tenant',
            ],
        ];

        $response = $this->postJson($this->baseUrl, $tenantData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'createdAt',
                    'updatedAt',
                    'deletedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => $tenantData['tenant']['name'],
                    'slug' => $tenantData['tenant']['slug'],
                ],
            ]);

        $this->assertDatabaseHas('tenants', $tenantData['tenant']);
    }

    public function test_cannot_create_tenant_with_duplicate_slug(): void
    {
        $existingTenant = Tenant::factory()->create();

        $tenantData = [
            'tenant' => [
                'name' => 'Test Tenant',
                'slug' => $existingTenant->slug,
            ],
        ];

        $response = $this->postJson($this->baseUrl, $tenantData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['tenant.slug']);
    }

    public function test_can_show_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $this->user->tenants()->attach($tenant, ['role' => RoleName::Admin->value]);

        $response = $this->getJson($this->baseUrl.'/'.$tenant->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'createdAt',
                    'updatedAt',
                    'deletedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                ],
            ]);
    }

    public function test_can_update_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $this->user->tenants()->attach($tenant, ['role' => RoleName::Admin->value]);
        $updateData = [
            'name' => 'Updated Name',
            'slug' => 'updated-slug',
        ];

        $response = $this->putJson($this->baseUrl.'/'.$tenant->id, $updateData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'createdAt',
                    'updatedAt',
                    'deletedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => $updateData['name'],
                    'slug' => $updateData['slug'],
                ],
            ]);

        $this->assertDatabaseHas('tenants', $updateData);
    }

    public function test_can_delete_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $this->user->tenants()->attach($tenant, ['role' => RoleName::Admin->value]);

        $response = $this->deleteJson($this->baseUrl.'/'.$tenant->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('tenants', ['id' => $tenant->id]);
    }

    public function test_returns404_for_nonexistent_tenant(): void
    {
        $response = $this->getJson($this->baseUrl.'/nonexistent-id');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_cannot_access_tenant_user_does_not_belong_to(): void
    {
        $unauthorizedTenant = Tenant::factory()->create();
        // Not attaching the tenant to the user

        $response = $this->getJson($this->baseUrl.'/'.$unauthorizedTenant->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
