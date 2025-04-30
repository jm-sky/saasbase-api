<?php

namespace Tests\Unit\Domain\Common\Models\Concerns;

use App\Domain\Auth\Models\User;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;
use Tests\Traits\WithMockedJwtPayload;

/**
 * @internal
 */
#[CoversNothing]
class BelongsToTenantTest extends TestCase
{
    use RefreshDatabase;
    use WithMockedJwtPayload;
    use WithAuthenticatedUser;

    private Contractor $model;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped();

        $this->user   = User::factory()->create();
        $this->tenant = Tenant::factory()->create();
        $this->model  = new Contractor();

        // Set current tenant context
        $this->authenticateUser($this->tenant, $this->user);
        $this->actingAs($this->user);
        $this->mockTenantId($this->tenant->id);
    }

    public function testModelIsScopedToTenant(): void
    {
        Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Test Model',
        ]);

        // Create another contractor for a different tenant, but in a new tenant context
        $otherTenant = Tenant::factory()->create();

        Contractor::factory()->create([
            'tenant_id' => $otherTenant->id,
            'name'      => 'Other Tenant Model',
        ]);

        $this->assertCount(1, Contractor::all());
        $this->assertEquals('Test Model', Contractor::first()->name);
    }

    // public function testModelAutomaticallySetsTenantIdOnCreate(): void
    // {
    //     $model = Contractor::factory()->create(['name' => 'Test Model']);

    //     $this->assertEquals($this->tenant->id, $model->tenant_id);
    // }

    // public function testCanQueryOnlyCurrentTenant(): void
    // {
    //     // Create record for current tenant
    //     Contractor::factory()->create([
    //         'tenant_id' => $this->tenant->id,
    //         'name'      => 'Test Model 1',
    //     ]);

    //     // Create record for other tenant in their context
    //     $otherTenant = Tenant::factory()->create();
    //     session(['current_tenant_id' => $otherTenant->id]);

    //     Contractor::factory()->create([
    //         'tenant_id' => $otherTenant->id,
    //         'name'      => 'Test Model 2',
    //     ]);

    //     // Switch back and verify we can only see our records
    //     session(['current_tenant_id' => $this->tenant->id]);
    //     $models = Contractor::forTenant($this->tenant->id)->get();

    //     $this->assertCount(1, $models);
    //     $this->assertEquals('Test Model 1', $models->first()->name);
    // }

    // public function testCanQueryDifferentTenant(): void
    // {
    //     $otherTenant = Tenant::factory()->create();

    //     $models = Contractor::forTenant($otherTenant->id)->get();

    //     $this->assertCount(0, $models);
    // }
}
