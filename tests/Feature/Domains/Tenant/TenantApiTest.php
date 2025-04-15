<?php

namespace Tests\Feature\Domains\Tenant;

use App\Domains\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantApiTest extends TestCase
{
    use RefreshDatabase;

    private string $baseUrl = '/api/tenants';

    public function test_can_list_tenants(): void
    {
        $tenants = Tenant::factory()->count(3)->create();

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'created_at',
                    'updated_at',
                    'deleted_at'
                ]
            ]);
    }

    public function test_can_create_tenant(): void
    {
        $tenantData = [
            'name' => 'Test Tenant',
            'slug' => 'test-tenant'
        ];

        $response = $this->postJson($this->baseUrl, $tenantData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'created_at',
                'updated_at',
                'deleted_at'
            ])
            ->assertJson([
                'name' => $tenantData['name'],
                'slug' => $tenantData['slug']
            ]);

        $this->assertDatabaseHas('tenants', $tenantData);
    }

    public function test_cannot_create_tenant_with_duplicate_slug(): void
    {
        $existingTenant = Tenant::factory()->create();

        $tenantData = [
            'name' => 'Test Tenant',
            'slug' => $existingTenant->slug
        ];

        $response = $this->postJson($this->baseUrl, $tenantData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_can_show_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->getJson($this->baseUrl . '/' . $tenant->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'created_at',
                'updated_at',
                'deleted_at'
            ])
            ->assertJson([
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug
            ]);
    }

    public function test_can_update_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $updateData = [
            'name' => 'Updated Name',
            'slug' => 'updated-slug'
        ];

        $response = $this->putJson($this->baseUrl . '/' . $tenant->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'created_at',
                'updated_at',
                'deleted_at'
            ])
            ->assertJson([
                'name' => $updateData['name'],
                'slug' => $updateData['slug']
            ]);

        $this->assertDatabaseHas('tenants', $updateData);
    }

    public function test_can_delete_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->deleteJson($this->baseUrl . '/' . $tenant->id);

        $response->assertStatus(204);
        $this->assertSoftDeleted('tenants', ['id' => $tenant->id]);
    }

    public function test_returns_404_for_nonexistent_tenant(): void
    {
        $response = $this->getJson($this->baseUrl . '/nonexistent-id');

        $response->assertStatus(404);
    }
}
