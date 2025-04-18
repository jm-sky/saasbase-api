<?php

namespace Tests\Feature\Domain\Products\Controllers;

use App\Domain\Products\Models\Product;
use App\Domain\Common\Models\MeasurementUnit;
use App\Domain\Common\Models\VatRate;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;
use PHPUnit\Framework\Attributes\Test;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private Tenant $tenant;
    private MeasurementUnit $unit;
    private VatRate $vatRate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->unit = MeasurementUnit::factory()->create();
        $this->vatRate = VatRate::factory()->create();
        $this->authenticateUser($this->tenant);
    }

    #[Test]
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

        $response = $this->getJson('/api/v1/products?filter[name]=Product A');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Product A');
    }

    #[Test]
    public function it_can_filter_products_by_unit_id(): void
    {
        $unit2 = MeasurementUnit::factory()->create();

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

        $response = $this->getJson("/api/v1/products?filter[unitId]={$this->unit->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.unitId', $this->unit->id);
    }

    #[Test]
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

        $response = $this->getJson('/api/v1/products?filter[createdAt][from]=2024-01-01&filter[createdAt][to]=2024-01-31');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $product1->id);
    }

    #[Test]
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

        $response = $this->getJson('/api/v1/products?sort=name');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Product A')
            ->assertJsonPath('data.1.name', 'Product B');
    }

    #[Test]
    public function it_validates_date_range_format(): void
    {
        $response = $this->getJson('/api/v1/products?filter[createdAt][from]=invalid-date');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['filter.createdAt.from']);
    }

    #[Test]
    public function it_validates_sort_parameter(): void
    {
        $response = $this->getJson('/api/v1/products?sort=invalid_field');

        $response->assertUnprocessable();
    }
}
