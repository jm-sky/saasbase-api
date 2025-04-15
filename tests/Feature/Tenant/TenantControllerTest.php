<?php

namespace Tests\Feature\Tenant;

use App\Domains\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_tenants(): void
    {
        $tenants = Tenant::factory()->count(3)->create();

        $response = $this->getJson('/api/tenants');

        $response->assertOk()
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_store_creates_new_tenant(): void
    {
        $data = [
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
        ];

        $response = $this->postJson('/api/tenants', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'created_at',
                'updated_at',
            ])
            ->assertJson($data);

        $this->assertDatabaseHas('tenants', $data);
    }

    public function test_show_returns_specific_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->getJson("/api/tenants/{$tenant->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'created_at',
                'updated_at',
            ])
            ->assertJson([
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ]);
    }

    public function test_update_modifies_existing_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $data = [
            'name' => 'Updated Tenant',
            'slug' => 'updated-tenant',
        ];

        $response = $this->putJson("/api/tenants/{$tenant->id}", $data);

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'created_at',
                'updated_at',
            ])
            ->assertJson($data);

        $this->assertDatabaseHas('tenants', $data);
    }

    public function test_destroy_deletes_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->deleteJson("/api/tenants/{$tenant->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('tenants', ['id' => $tenant->id]);
    }
}
