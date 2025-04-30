<?php

namespace Tests\Feature\Domain\Products;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\MeasurementUnit;
use App\Domain\Common\Models\VatRate;
use App\Domain\Products\Models\Product;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversNothing]
class ProductApiTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/products';

    private Tenant $tenant;

    private MeasurementUnit $unit;

    private VatRate $vatRate;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped('Fix tenancy with JWT');

        $this->tenant = Tenant::factory()->create();
        $this->user   = $this->authenticateUser($this->tenant);

        $this->unit    = MeasurementUnit::factory()->create();
        $this->vatRate = VatRate::factory()->create();
    }

    public function testCanListProducts(): void
    {
        // Create products for the current tenant
        Product::factory()
            ->count(3)
            ->create([
                'tenant_id'   => $this->tenant->id,
                'unit_id'     => $this->unit->id,
                'vat_rate_id' => $this->vatRate->id,
            ])
        ;

        // Create products for a different tenant
        $otherTenant = Tenant::factory()->create();
        Product::factory()
            ->count(2)
            ->create([
                'tenant_id'   => $otherTenant->id,
                'unit_id'     => $this->unit->id,
                'vat_rate_id' => $this->vatRate->id,
            ])
        ;

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_OK)
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
                        'deletedAt',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ])
        ;
    }

    public function testCanCreateProduct(): void
    {
        $productData = [
            'tenantId'    => $this->tenant->id,
            'name'        => 'Test Product',
            'description' => 'Test Description',
            'unitId'      => $this->unit->id,
            'priceNet'    => 100.50,
            'vatRateId'   => $this->vatRate->id,
        ];

        $response = $this->postJson($this->baseUrl, $productData);

        $response->assertStatus(Response::HTTP_CREATED)
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
                'deletedAt',
            ])
            ->assertJson([
                'name'        => $productData['name'],
                'description' => $productData['description'],
                'priceNet'    => $productData['priceNet'],
            ])
        ;

        $this->assertDatabaseHas('products', [
            'tenant_id'   => $productData['tenantId'],
            'name'        => $productData['name'],
            'description' => $productData['description'],
            'unit_id'     => $productData['unitId'],
            'price_net'   => $productData['priceNet'],
            'vat_rate_id' => $productData['vatRateId'],
        ]);
    }

    public function testCannotCreateProductWithInvalidData(): void
    {
        $productData = [
            'tenantId'    => 'invalid-uuid',
            'name'        => '',
            'description' => '',
            'unitId'      => 'invalid-uuid',
            'priceNet'    => 'invalid-price',
            'vatRateId'   => 'invalid-uuid',
        ];

        $response = $this->postJson($this->baseUrl, $productData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'tenantId',
                'name',
                'unitId',
                'priceNet',
                'vatRateId',
            ])
        ;
    }

    public function testCanShowProduct(): void
    {
        $product = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'unit_id'     => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        $response = $this->getJson($this->baseUrl . '/' . $product->id);

        $response->assertStatus(Response::HTTP_OK)
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
                'deletedAt',
            ])
            ->assertJson([
                'id'          => $product->id,
                'name'        => $product->name,
                'description' => $product->description,
                'priceNet'    => $product->price_net,
            ])
        ;
    }

    public function testCanUpdateProduct(): void
    {
        $product = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'unit_id'     => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        $updateData = [
            'tenantId'    => $this->tenant->id,
            'name'        => 'Updated Product',
            'description' => 'Updated Description',
            'unitId'      => $this->unit->id,
            'priceNet'    => 200.75,
            'vatRateId'   => $this->vatRate->id,
        ];

        $response = $this->putJson($this->baseUrl . '/' . $product->id, $updateData);

        $response->assertStatus(Response::HTTP_OK)
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
                'deletedAt',
            ])
            ->assertJson([
                'name'        => $updateData['name'],
                'description' => $updateData['description'],
                'priceNet'    => $updateData['priceNet'],
            ])
        ;

        $this->assertDatabaseHas('products', [
            'id'          => $product->id,
            'name'        => $updateData['name'],
            'description' => $updateData['description'],
            'unit_id'     => $updateData['unitId'],
            'price_net'   => $updateData['priceNet'],
            'vat_rate_id' => $updateData['vatRateId'],
        ]);
    }

    public function testCanDeleteProduct(): void
    {
        $product = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'unit_id'     => $this->unit->id,
            'vat_rate_id' => $this->vatRate->id,
        ]);

        $response = $this->deleteJson($this->baseUrl . '/' . $product->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function testReturns404ForNonexistentProduct(): void
    {
        $response = $this->getJson($this->baseUrl . '/nonexistent-id');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
