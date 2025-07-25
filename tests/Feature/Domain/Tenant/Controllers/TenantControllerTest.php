<?php

namespace Tests\Feature\Domain\Tenant\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Controllers\TenantController;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(TenantController::class)]
class TenantControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped('Need to implement filter logic');

        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function testIndexReturnsAllTenants(): void
    {
        // Clear any existing tenants
        Tenant::query()->forceDelete();

        $tenants = Tenant::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/tenants');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'createdAt',
                        'updatedAt',
                    ],
                ],
            ])
        ;

        // Verify we got exactly the tenants we created
        $responseIds = collect($response->json('data'))->pluck('id')->sort()->values();
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
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'createdAt',
                    'updatedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                ],
            ])
        ;

        $this->assertDatabaseHas('tenants', $data);
    }

    public function testShowReturnsSpecificTenant(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'createdAt',
                    'updatedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id'   => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                ],
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
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'createdAt',
                    'updatedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                ],
            ])
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
