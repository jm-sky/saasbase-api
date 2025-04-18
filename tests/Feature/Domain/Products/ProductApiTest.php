<?php

namespace Tests\Feature\Domain\Products;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\Unit;
use App\Domain\Common\Models\VatRate;
use App\Domain\Products\Models\Product;
use App\Domain\Tenant\Models\{Tenant, UserTenant};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private string $baseUrl = '/api/v1/products';
    private Tenant $tenant;
    private Unit $unit;
    private VatRate $vatRate;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();

        // Create tenant membership and set as current tenant
        $this->user->tenants()->attach($this->tenant->id, ['role' => 'member']);
        session(['current_tenant_id' => $this->tenant->id]);

        $this->unit = Unit::factory()->create();
        $this->vatRate = VatRate::factory()->create();

        // Authenticate user after setting up tenant
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_products(): void
    {
        // Create products for the current tenant
        Product::factory()
            ->count(3)
            ->create([
                'tenant_id' => $this->tenant->id,
                'unit_id' => $this->unit->id,
                'vat_rate_id' => $this->vatRate->id,
            ]);

        // Create products for a different tenant
        $otherTenant = Tenant::factory()->create();
        Product::factory()
            ->count(2)
            ->create([
                'tenant_id' => $otherTenant->id,
                'unit_id' => $this->unit->id,
                'vat_rate_id' => $this->vatRate->id,
            ]);

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'tenantId',
                        'name',
                        'description',
                        'unitId',
                        'priceNet',
                        'vatRateId',
                        'createdAt',
                        'updatedAt',
                        'deletedAt'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);
    }

    public function test_can_create_product(): void
    {
        $productData = [
            'tenantId' => $this->tenant->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'unitId' => $this->unit->id,
            'priceNet' => 100.50,
            'vatRateId' => $this->vatRate->id,
        ];

        $response = $this->postJson($this->baseUrl, $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'tenantId',
                'name',
                'description',
                'unitId',
                'priceNet',
                'vatRateId',
                'createdAt',
                'updatedAt',
                'deletedAt'
            ])
            ->assertJson([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'priceNet' => $productData['priceNet'],
            ]);

        $this->assertDatabaseHas('products', [
            'tenant_id' => $this->tenant->id,
            'name' => $productData['name'],
            'description' => $productData['description'],
            'unit_id' => $this->unit->id,
            'price_net' => $productData['priceNet'],
            'vat_rate_id' => $this->vatRate->id,
        ]);
    }

    public function test_cannot_create_product_with_invalid_data(): void
    {
        $productData = [
            'tenantId' => 'invalid-uuid',
            'name' => '',
            'unitId' => 'invalid-uuid',
            'priceNet' => -100,
            'vatRateId' => 'invalid-uuid',
        ];

        $response = $this->postJson($this->baseUrl, $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'tenantId',
                'name',
                'unitId',
                'priceNet',
                'vatRateId',
            ]);
    }

    public function test_can_show_product(): void
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'unit_id' => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        $response = $this->getJson($this->baseUrl . '/' . $product->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'tenantId',
                'name',
                'description',
                'unitId',
                'priceNet',
                'vatRateId',
                'createdAt',
                'updatedAt',
                'deletedAt'
            ])
            ->assertJson([
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'priceNet' => $product->price_net,
            ]);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'unit_id' => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        $updateData = [
            'tenantId' => $this->tenant->id,
            'name' => 'Updated Product',
            'description' => 'Updated Description',
            'unitId' => $this->unit->id,
            'priceNet' => 200.75,
            'vatRateId' => $this->vatRate->id,
        ];

        $response = $this->putJson($this->baseUrl . '/' . $product->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'tenantId',
                'name',
                'description',
                'unitId',
                'priceNet',
                'vatRateId',
                'createdAt',
                'updatedAt',
                'deletedAt'
            ])
            ->assertJson([
                'name' => $updateData['name'],
                'description' => $updateData['description'],
                'priceNet' => $updateData['priceNet'],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $updateData['name'],
            'description' => $updateData['description'],
            'unit_id' => $updateData['unitId'],
            'price_net' => $updateData['priceNet'],
            'vat_rate_id' => $updateData['vatRateId'],
        ]);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'unit_id' => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        $response = $this->deleteJson($this->baseUrl . '/' . $product->id);

        $response->assertStatus(204);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_returns_404_for_nonexistent_product(): void
    {
        $response = $this->getJson($this->baseUrl . '/nonexistent-id');

        $response->assertStatus(404);
    }
}
