<?php

namespace Tests\Feature\Domain\Products\Controllers;

use App\Domain\Products\Models\Product;
use App\Domain\Common\Models\Unit;
use App\Domain\Common\Models\VatRate;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private Tenant $tenant;
    private Unit $unit;
    private VatRate $vatRate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->unit = Unit::factory()->create();
        $this->vatRate = VatRate::factory()->create();
        $this->authenticateUser($this->tenant);
    }

    /** @test */
    public function it_can_filter_products_by_name(): void
    {
        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Product A',
            'unit_id' => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Product B',
            'unit_id' => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        $response = $this->getJson('/api/products?filter[name]=Product A');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Product A');
    }

    /** @test */
    public function it_can_filter_products_by_unit_id(): void
    {
        $unit2 = Unit::factory()->create();

        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'unit_id' => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'unit_id' => $unit2->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        $response = $this->getJson("/api/products?filter[unitId]={$this->unit->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.unitId', $this->unit->id);
    }

    /** @test */
    public function it_can_filter_products_by_date_range(): void
    {
        $product1 = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'unit_id' => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
            'created_at' => '2024-01-01 12:00:00',
        ]);

        $product2 = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'unit_id' => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
            'created_at' => '2024-02-01 12:00:00',
        ]);

        $response = $this->getJson('/api/products?filter[createdAt]=2024-01-01,2024-01-31');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $product1->id);
    }

    /** @test */
    public function it_can_sort_products(): void
    {
        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Product B',
            'unit_id' => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Product A',
            'unit_id' => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        $response = $this->getJson('/api/products?sort=name');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Product A')
            ->assertJsonPath('data.1.name', 'Product B');
    }

    /** @test */
    public function it_validates_date_range_format(): void
    {
        $response = $this->getJson('/api/products?filter[createdAt]=invalid-date');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['filter.createdAt']);
    }

    /** @test */
    public function it_validates_sort_parameter(): void
    {
        $response = $this->getJson('/api/products?sort=invalid');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['sort']);
    }
}
