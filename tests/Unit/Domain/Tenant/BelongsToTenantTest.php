<?php

namespace Tests\Unit\Domain\Tenant;

use App\Domain\Auth\Models\User;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

/**
 * @internal
 *
 * @covers \App\Domain\Tenant\Traits\BelongsToTenant
 */
#[CoversClass(BelongsToTenant::class)]
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

        $this->user = User::factory()->create();
        $this->tenant = Tenant::factory()->create();
        $this->otherTenant = Tenant::factory()->create();
        $this->model = new Contractor;

        $this->user->tenants()->attach($this->tenant, ['role' => RoleName::Admin->value]);

        Tenant::$BYPASSED_TENANT_ID = $this->tenant->id;
    }

    public function test_model_is_scoped_to_tenant(): void
    {
        Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Model',
        ]);

        Contractor::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Other Tenant Model',
        ]);

        $this->assertCount(1, Contractor::all());
        $this->assertEquals('Test Model', Contractor::first()->name);
    }

    public function test_model_automatically_sets_tenant_id(): void
    {
        $model = Contractor::factory()->create(['name' => 'Test Model']);
        $this->assertEquals($this->tenant->id, $model->tenant_id);
    }

    public function test_can_create_record_for_different_tenant(): void
    {
        $model = Contractor::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Test Model',
        ]);

        $this->assertEquals($this->otherTenant->id, $model->tenant_id);
    }

    public function test_can_bypass_tenant_scope_with_callback(): void
    {
        // Create records for both tenants
        Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Model',
        ]);

        Contractor::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Other Tenant Model',
        ]);

        $this->assertCount(1, Contractor::all());

        // But we can see all records when bypassing scope
        $this->assertCount(2, Contractor::withoutTenant()->get());
    }

    public function test_can_query_specific_tenant_when_bypassing_scope(): void
    {
        // Create records for both tenants
        Contractor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Model',
        ]);

        Contractor::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Other Tenant Model',
        ]);

        /** @var Collection<int, Contractor> $otherTenantModels */
        $otherTenantModels = Contractor::withoutTenant()
            ->where('tenant_id', $this->otherTenant->id)
            ->get();

        $this->assertCount(1, $otherTenantModels);
        $this->assertEquals('Other Tenant Model', $otherTenantModels->first()->name);
    }
}
