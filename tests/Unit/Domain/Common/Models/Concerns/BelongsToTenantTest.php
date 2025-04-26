<?php

namespace Tests\Unit\Domain\Common\Models\Concerns;

use App\Domain\Auth\Models\User;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;
use Tests\Traits\WithMockedJwtPayload;

/**
 * @internal
 *
 * @coversNothing
 */
class BelongsToTenantTest extends TestCase
{
    use RefreshDatabase;
    use WithMockedJwtPayload;

    private Contractor $model;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->model  = new Contractor();
        $this->user   = User::factory()->create();

        // Set current tenant context
        session(['current_tenant_id' => $this->tenant->id]);
        $this->actingAs($this->user);
    }

    public function testModelIsScopedToTenant(): void
    {
        Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Test Model',
        ]);

        // Create another contractor for a different tenant, but in a new tenant context
        $otherTenant = Tenant::factory()->create();
        session(['current_tenant_id' => $otherTenant->id]);

        Contractor::factory()->create([
            'tenant_id' => $otherTenant->id,
            'name'      => 'Other Tenant Model',
        ]);

        // Switch back to original tenant
        session(['current_tenant_id' => $this->tenant->id]);

        $this->assertCount(1, Contractor::all());
        $this->assertEquals('Test Model', Contractor::first()->name);
    }

    public function testModelAutomaticallySetsTenantIdOnCreate(): void
    {
        // $this->authenticateUser($this->tenant);
        // $this->mockTenantId($this->tenant->id); TODO: Use instead of sessions
        $model = Contractor::factory()->create(['name' => 'Test Model']);

        $this->assertEquals($this->tenant->id, $model->tenant_id);
    }

    public function testCanQueryOnlyCurrentTenant(): void
    {
        // Create record for current tenant
        Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Test Model 1',
        ]);

        // Create record for other tenant in their context
        $otherTenant = Tenant::factory()->create();
        session(['current_tenant_id' => $otherTenant->id]);

        Contractor::factory()->create([
            'tenant_id' => $otherTenant->id,
            'name'      => 'Test Model 2',
        ]);

        // Switch back and verify we can only see our records
        session(['current_tenant_id' => $this->tenant->id]);
        $models = Contractor::forTenant($this->tenant->id)->get();

        $this->assertCount(1, $models);
        $this->assertEquals('Test Model 1', $models->first()->name);
    }

    public function testCanQueryDifferentTenant(): void
    {
        $otherTenant = Tenant::factory()->create();

        $models = Contractor::forTenant($otherTenant->id)->get();

        $this->assertCount(0, $models);
    }
}
