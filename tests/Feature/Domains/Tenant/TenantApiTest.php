<?php

namespace Tests\Feature\Domains\Tenant;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class TenantApiTest extends TestCase
{
    use RefreshDatabase;

    private string $baseUrl = '/api/v1/tenants';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function testCanListTenants(): void
    {
        // Clear any existing tenants to ensure clean state
        Tenant::query()->forceDelete();

        $tenants = Tenant::factory()->count(3)->create();

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'createdAt',
                    'updatedAt',
                    'deletedAt',
                ],
            ])
        ;

        // Verify we got exactly the tenants we created
        $responseIds = collect($response->json())->pluck('id')->sort()->values();
        $expectedIds = $tenants->pluck('id')->sort()->values();
        $this->assertEquals($expectedIds, $responseIds);
    }

    public function testCanCreateTenant(): void
    {
        $tenantData = [
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
        ];

        $response = $this->postJson($this->baseUrl, $tenantData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'createdAt',
                'updatedAt',
                'deletedAt',
            ])
            ->assertJson([
                'name' => $tenantData['name'],
                'slug' => $tenantData['slug'],
            ])
        ;

        $this->assertDatabaseHas('tenants', $tenantData);
    }

    public function testCannotCreateTenantWithDuplicateSlug(): void
    {
        $existingTenant = Tenant::factory()->create();

        $tenantData = [
            'name' => 'Test Tenant',
            'slug' => $existingTenant->slug,
        ];

        $response = $this->postJson($this->baseUrl, $tenantData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug'])
        ;
    }

    public function testCanShowTenant(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->getJson($this->baseUrl . '/' . $tenant->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'createdAt',
                'updatedAt',
                'deletedAt',
            ])
            ->assertJson([
                'id'   => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ])
        ;
    }

    public function testCanUpdateTenant(): void
    {
        $tenant     = Tenant::factory()->create();
        $updateData = [
            'name' => 'Updated Name',
            'slug' => 'updated-slug',
        ];

        $response = $this->putJson($this->baseUrl . '/' . $tenant->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'createdAt',
                'updatedAt',
                'deletedAt',
            ])
            ->assertJson([
                'name' => $updateData['name'],
                'slug' => $updateData['slug'],
            ])
        ;

        $this->assertDatabaseHas('tenants', $updateData);
    }

    public function testCanDeleteTenant(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->deleteJson($this->baseUrl . '/' . $tenant->id);

        $response->assertStatus(204);
        $this->assertSoftDeleted('tenants', ['id' => $tenant->id]);
    }

    public function testReturns404ForNonexistentTenant(): void
    {
        $response = $this->getJson($this->baseUrl . '/nonexistent-id');

        $response->assertStatus(404);
    }
}
