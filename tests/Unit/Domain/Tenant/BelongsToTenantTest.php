<?php

namespace Tests\Unit\Domain\Tenant;

use App\Domain\Auth\Models\User;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversNothing]
class BelongsToTenantTest extends TestCase
{
    use RefreshDatabase;

    private Contractor $model;

    private User $user;

    private Tenant $tenant;

    private Tenant $otherTenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user        = User::factory()->create();
        $this->tenant      = Tenant::factory()->create();
        $this->otherTenant = Tenant::factory()->create();
        $this->model       = new Contractor();

        $this->user->tenants()->attach($this->tenant, ['role' => 'admin']);

        Tenant::$BYPASSED_TENANT_ID = $this->tenant->id;
    }

    public function testModelIsScopedToTenant(): void
    {
        Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Test Model',
        ]);

        Contractor::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'name'      => 'Other Tenant Model',
        ]);

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
