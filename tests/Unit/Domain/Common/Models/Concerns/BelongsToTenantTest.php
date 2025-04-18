<?php

namespace Tests\Unit\Domain\Common\Models\Concerns;

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
class BelongsToTenantTest extends TestCase
{
    use RefreshDatabase;

    private Contractor $model;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->model  = new Contractor();
    }

    public function testModelIsScopedToTenant(): void
    {
        session(['current_tenant_id' => $this->tenant->id]);

        Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Test Model',
        ]);

        Contractor::factory()->create([
            'tenant_id' => Tenant::factory()->create()->id,
            'name'      => 'Other Tenant Model',
        ]);

        $this->assertCount(1, Contractor::all());
        $this->assertEquals('Test Model', Contractor::first()->name);
    }

    public function testModelAutomaticallySetsTenantIdOnCreate(): void
    {
        session(['current_tenant_id' => $this->tenant->id]);

        $model = Contractor::factory()->create(['name' => 'Test Model']);

        $this->assertEquals($this->tenant->id, $model->tenant_id);
    }

    public function testCanExplicitlySetTenantId(): void
    {
        $otherTenant = Tenant::factory()->create();

        $model = Contractor::factory()->create([
            'tenant_id' => $otherTenant->id,
            'name'      => 'Test Model',
        ]);

        $this->assertEquals($otherTenant->id, $model->tenant_id);
    }

    public function testCanQueryForSpecificTenant(): void
    {
        $otherTenant = Tenant::factory()->create();

        Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Test Model 1',
        ]);

        Contractor::factory()->create([
            'tenant_id' => $otherTenant->id,
            'name'      => 'Test Model 2',
        ]);

        $models = Contractor::forTenant($otherTenant->id)->get();

        $this->assertCount(1, $models);
        $this->assertEquals('Test Model 2', $models->first()->name);
    }
}
