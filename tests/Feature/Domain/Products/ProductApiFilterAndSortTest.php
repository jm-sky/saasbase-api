<?php

namespace Tests\Feature\Domain\Products;

use App\Domain\Common\Models\MeasurementUnit;
use App\Domain\Common\Models\VatRate;
use App\Domain\Products\Models\Product;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversNothing]
class ProductApiFilterAndSortTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private Tenant $tenant;

    private MeasurementUnit $unit;

    private VatRate $vatRate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant  = Tenant::factory()->create();

        $this->authenticateUser($this->tenant);

        Tenant::bypassTenant($this->tenant->id, function () {
            $this->unit = MeasurementUnit::factory()->create();
        });

        $this->vatRate = VatRate::factory()->create();
    }

    #[Test]
    public function itCanFilterProductsByName(): void
    {
        Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'name'        => 'Product A',
            'unit_id'     => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'name'        => 'Product B',
            'unit_id'     => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        $response = $this->getJson('/api/v1/products?filter[name]=Product A');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Product A')
        ;
    }

    #[Test]
    public function itCanFilterProductsByUnitId(): void
    {
        $unit2 = Tenant::bypassTenant($this->tenant->id, function () {
            return $unit2 = MeasurementUnit::factory()->create();
        });

        Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'unit_id'     => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'unit_id'     => $unit2->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        $response = $this->getJson("/api/v1/products?filter[unitId]={$this->unit->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.unitId', $this->unit->id)
        ;
    }

    #[Test]
    public function itCanFilterProductsByDateRange(): void
    {
        $product1 = Tenant::bypassTenant($this->tenant->id, function () {
            $product1 = Product::factory()->create([
                'tenant_id'   => $this->tenant->id,
                'unit_id'     => $this->unit->id,
                'vat_rate_id' => $this->vatRate->id,
                'created_at'  => '2024-01-01 12:00:00',
            ]);

            Product::factory()->create([
                'tenant_id'   => $this->tenant->id,
                'unit_id'     => $this->unit->id,
                'vat_rate_id' => $this->vatRate->id,
                'created_at'  => '2024-02-01 12:00:00',
            ]);

            return $product1;
        });

        $response = $this->getJson('/api/v1/products?filter[createdAt][from]=2024-01-01&filter[createdAt][to]=2024-01-31');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $product1->id)
        ;
    }

    #[Test]
    public function itCanSortProducts(): void
    {
        Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'name'        => 'Product B',
            'unit_id'     => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'name'        => 'Product A',
            'unit_id'     => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        $response = $this->getJson('/api/v1/products?sort=name');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Product A')
            ->assertJsonPath('data.1.name', 'Product B')
        ;
    }

    #[Test]
    public function itValidatesDateRangeFormat(): void
    {
        $response = $this->getJson('/api/v1/products?filter[createdAt][from]=invalid-date');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['filter.createdAt.from'])
        ;
    }

    #[Test]
    public function itValidatesSortParameter(): void
    {
        $response = $this->getJson('/api/v1/products?sort=invalid_field');

        $response->assertUnprocessable();
    }
}
