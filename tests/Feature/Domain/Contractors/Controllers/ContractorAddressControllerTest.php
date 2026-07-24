<?php

namespace Tests\Feature\Domain\Contractors\Controllers;

use App\Domain\Common\Enums\AddressType;
use App\Domain\Contractors\Controllers\ContractorAddressController;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Models\ContractorAddress;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;
use Tests\Traits\WithCountries;

/**
 * @internal
 */
#[CoversClass(ContractorAddressController::class)]
class ContractorAddressControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;
    use WithCountries;

    private Tenant $tenant;

    private Contractor $contractor;

    private string $baseUrl = '/api/v1/contractors';

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->contractor = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->seedCountries();
        $this->authenticateUser($this->tenant);
    }

    public function test_can_list_contractor_addresses(): void
    {
        ContractorAddress::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'addressable_id' => $this->contractor->id,
            'addressable_type' => Contractor::class,
        ]);

        $response = $this->getJson("{$this->baseUrl}/{$this->contractor->id}/addresses");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'street', 'city', 'postalCode', 'country', 'tenantId',
                        'building', 'flat', 'description', 'type', 'isDefault',
                    ],
                ],
            ]);
    }

    public function test_can_create_contractor_address(): void
    {
        $data = [
            'street' => '123 Main St',
            'city' => 'Test City',
            'postalCode' => '12345',
            'country' => self::SECONDARY_COUNTRY,
            'tenantId' => $this->tenant->id,
            'building' => 'Building A',
            'flat' => '42',
            'description' => 'Main office',
            'type' => AddressType::REGISTERED_OFFICE->value,
            'isDefault' => true,
        ];

        $response = $this->postJson("{$this->baseUrl}/{$this->contractor->id}/addresses", $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id', 'street', 'city', 'postalCode', 'country', 'tenantId',
                    'building', 'flat', 'description', 'type', 'isDefault',
                ],
            ]);

        $this->assertDatabaseHas('addresses', [
            'street' => '123 Main St',
            'city' => 'Test City',
            'postal_code' => '12345',
            'is_default' => true,
            'type' => AddressType::REGISTERED_OFFICE->value,
            'addressable_id' => $this->contractor->id,
            'addressable_type' => Contractor::class,
        ]);
    }

    public function test_can_show_contractor_address(): void
    {
        $address = ContractorAddress::factory()->create([
            'tenant_id' => $this->tenant->id,
            'addressable_id' => $this->contractor->id,
            'addressable_type' => Contractor::class,
        ]);

        $response = $this->getJson("{$this->baseUrl}/{$this->contractor->id}/addresses/{$address->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'street', 'city', 'postalCode', 'country', 'tenantId',
                    'building', 'flat', 'description', 'type', 'isDefault',
                ],
            ]);
    }

    public function test_cannot_show_address_of_different_contractor(): void
    {
        $otherContractor = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);
        $address = ContractorAddress::factory()->create([
            'tenant_id' => $this->tenant->id,
            'addressable_id' => $otherContractor->id,
            'addressable_type' => Contractor::class,
        ]);

        $response = $this->getJson("{$this->baseUrl}/{$this->contractor->id}/addresses/{$address->id}");

        $response->assertNotFound();
    }

    public function test_can_update_contractor_address(): void
    {
        $address = ContractorAddress::factory()->create([
            'tenant_id' => $this->tenant->id,
            'addressable_id' => $this->contractor->id,
            'addressable_type' => Contractor::class,
        ]);

        $data = [
            'street' => '456 Updated St',
            'city' => 'Updated City',
            'postalCode' => '54321',
            'country' => self::SECONDARY_COUNTRY,
            'tenantId' => $this->tenant->id,
            'building' => 'Building B',
            'flat' => '24',
            'description' => 'Branch office',
            'type' => AddressType::CORRESPONDENCE->value,
            'isDefault' => false,
        ];

        $response = $this->putJson("{$this->baseUrl}/{$this->contractor->id}/addresses/{$address->id}", $data);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'street', 'city', 'postalCode', 'country', 'tenantId',
                    'building', 'flat', 'description', 'type', 'isDefault',
                ],
            ]);

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'street' => '456 Updated St',
            'city' => 'Updated City',
            'postal_code' => '54321',
            'is_default' => false,
            'type' => AddressType::CORRESPONDENCE->value,
            'addressable_id' => $this->contractor->id,
            'addressable_type' => Contractor::class,
        ]);
    }

    public function test_cannot_update_address_of_different_contractor(): void
    {
        $otherContractor = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);
        $address = ContractorAddress::factory()->create([
            'tenant_id' => $this->tenant->id,
            'addressable_id' => $otherContractor->id,
            'addressable_type' => Contractor::class,
        ]);

        $data = [
            'street' => '456 Updated St',
            'city' => 'Updated City',
            'postalCode' => '54321',
            'country' => self::SECONDARY_COUNTRY,
            'tenantId' => $this->tenant->id,
            'type' => AddressType::CORRESPONDENCE->value,
        ];

        $response = $this->putJson("{$this->baseUrl}/{$this->contractor->id}/addresses/{$address->id}", $data);

        $response->assertNotFound();
    }

    public function test_can_delete_contractor_address(): void
    {
        $address = ContractorAddress::factory()->create([
            'tenant_id' => $this->tenant->id,
            'addressable_id' => $this->contractor->id,
            'addressable_type' => Contractor::class,
        ]);

        $response = $this->deleteJson("{$this->baseUrl}/{$this->contractor->id}/addresses/{$address->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_cannot_delete_address_of_different_contractor(): void
    {
        $otherContractor = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);
        $address = ContractorAddress::factory()->create([
            'tenant_id' => $this->tenant->id,
            'addressable_id' => $otherContractor->id,
            'addressable_type' => Contractor::class,
        ]);

        $response = $this->deleteJson("{$this->baseUrl}/{$this->contractor->id}/addresses/{$address->id}");

        $response->assertNotFound();
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }
}
