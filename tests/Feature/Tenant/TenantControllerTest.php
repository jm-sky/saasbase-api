<?php

namespace Tests\Feature\Tenant;

use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class TenantControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsAllTenants(): void
    {
        // Clear any existing tenants
        Tenant::query()->forceDelete();

        $tenants = Tenant::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/tenants');

        $response->assertOk()
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'createdAt',
                    'updatedAt',
                ],
            ])
        ;

        // Verify we got exactly the tenants we created
        $responseIds = collect($response->json())->pluck('id')->sort()->values();
        $expectedIds = $tenants->pluck('id')->sort()->values();
        $this->assertEquals($expectedIds, $responseIds);
    }

    public function testStoreCreatesNewTenant(): void
    {
        $data = [
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
        ];

        $response = $this->postJson('/api/v1/tenants', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'createdAt',
                'updatedAt',
            ])
            ->assertJson($data)
        ;

        $this->assertDatabaseHas('tenants', $data);
    }

    public function testShowReturnsSpecificTenant(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'createdAt',
                'updatedAt',
            ])
            ->assertJson([
                'id'   => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ])
        ;
    }

    public function testUpdateModifiesExistingTenant(): void
    {
        $tenant = Tenant::factory()->create();
        $data   = [
            'name' => 'Updated Tenant',
            'slug' => 'updated-tenant',
        ];

        $response = $this->putJson("/api/v1/tenants/{$tenant->id}", $data);

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'slug',
                'createdAt',
                'updatedAt',
            ])
            ->assertJson($data)
        ;

        $this->assertDatabaseHas('tenants', $data);
    }

    public function testDestroyDeletesTenant(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->deleteJson("/api/v1/tenants/{$tenant->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('tenants', ['id' => $tenant->id]);
    }
}
