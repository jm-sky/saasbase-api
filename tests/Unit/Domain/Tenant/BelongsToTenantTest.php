<?php

namespace Tests\Unit\Domain\Tenant;

use App\Domain\Contractors\Models\Contractor;
use App\Domain\Tenant\Exceptions\TenantNotFoundException;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class BelongsToTenantTest extends TestCase
{
    use RefreshDatabase;

    private Contractor $model;

    private Tenant $tenant;

    private Tenant $otherTenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant      = Tenant::factory()->create();
        $this->otherTenant = Tenant::factory()->create();
        $this->model       = new Contractor();

        // Set current tenant context
        session(['current_tenant_id' => $this->tenant->id]);
    }

    public function testModelIsScopedToTenant(): void
    {
        // Create records for both tenants
        Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Test Model',
        ]);

        Contractor::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'name'      => 'Other Tenant Model',
        ]);

        // Should only see records for current tenant
        $this->assertCount(1, Contractor::all());
        $this->assertEquals('Test Model', Contractor::first()->name);
    }

    public function testModelAutomaticallySetsTenantId(): void
    {
        $model = Contractor::factory()->create(['name' => 'Test Model']);
        $this->assertEquals($this->tenant->id, $model->tenant_id);
    }

    public function testCanCreateRecordForDifferentTenant(): void
    {
        $model = Contractor::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'name'      => 'Test Model',
        ]);

        $this->assertEquals($this->otherTenant->id, $model->tenant_id);
    }

    public function testThrowsExceptionWhenTenantContextNotFound(): void
    {
        session()->forget('current_tenant_id');

        $this->expectException(TenantNotFoundException::class);

        Contractor::factory()->create(['name' => 'Test Model']);
    }

    public function testCanBypassTenantScopeWithCallback(): void
    {
        // Create records for both tenants
        Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Test Model',
        ]);

        Contractor::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'name'      => 'Other Tenant Model',
        ]);

        // Normal query should still be scoped
        $this->assertCount(1, Contractor::all());

        // But we can see all records when bypassing scope
        $this->assertCount(2, Contractor::withoutTenant()->get());
    }

    public function testCanQuerySpecificTenantWhenBypassingScope(): void
    {
        // Create records for both tenants
        Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Test Model',
        ]);

        Contractor::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'name'      => 'Other Tenant Model',
        ]);

        $otherTenantModels = Contractor::withoutTenant()
            ->where('tenant_id', $this->otherTenant->id)
            ->get()
        ;

        $this->assertCount(1, $otherTenantModels);
        $this->assertEquals('Other Tenant Model', $otherTenantModels->first()->name);
    }
}
