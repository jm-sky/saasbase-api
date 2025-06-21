<?php

namespace Tests\Unit\Domain\Common\Models\Concerns;

use Tests\TestCase;
use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use App\Domain\Contractors\Models\Contractor;
use PHPUnit\Framework\Attributes\CoversNothing;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @internal
 */
#[CoversNothing]
class BelongsToTenantTest extends TestCase
{
    use RefreshDatabase;

    private Contractor $model;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user   = User::factory()->create();
        $this->tenant = Tenant::factory()->create();
        $this->model  = new Contractor();
    }

    public function testModelIsScopedToTenant(): void
    {
        Tenant::bypassTenant($this->tenant->id, function () {
            Contractor::factory()->create([
                'name' => 'Test Model',
            ]);
        });

        $otherTenant = Tenant::factory()->create();

        Tenant::bypassTenant($otherTenant->id, function () {
            Contractor::factory()->create([
                'name' => 'Other Tenant Model',
            ]);
        });

        Tenant::bypassTenant($this->tenant->id, function () {
            $this->assertCount(1, Contractor::all());
            $this->assertEquals('Test Model', Contractor::first()->name);
        });
    }

    public function testModelAutomaticallySetsTenantIdOnCreate(): void
    {
        Tenant::bypassTenant($this->tenant->id, function () {
            $model = Contractor::factory()->create(['name' => 'Test Model']);

            $this->assertEquals($this->tenant->id, $model->tenant_id);
        });
    }

    public function testCanQueryOnlyCurrentTenant(): void
    {
        Tenant::bypassTenant($this->tenant->id, function () {
            Contractor::factory()->create([
                'name' => 'Test Model 1',
            ]);
        });

        $otherTenant = Tenant::factory()->create();
        Tenant::bypassTenant($otherTenant->id, function () {
            Contractor::factory()->create([
                'name' => 'Test Model 2',
            ]);
        });

        /** @var Collection<int, Contractor> $models */
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
