<?php

namespace Tests\Feature\Domain\Contractors\Controllers;

use App\Domain\Common\Enums\AddressType;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Models\ContractorAddress;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversNothing]
class ContractorAddressControllerTest extends TestCase
{
    use RefreshDatabase;

    use WithAuthenticatedUser;

    private Tenant $tenant;

    private Contractor $contractor;

    private string $baseUrl = '/api/v1/contractors';

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant     = Tenant::factory()->create();
        $this->contractor = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->authenticateUser($this->tenant);
    }

    public function testCanListContractorAddresses(): void
    {
        ContractorAddress::factory()->count(3)->create([
            'tenant_id'        => $this->tenant->id,
            'addressable_id'   => $this->contractor->id,
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
            ])
        ;
    }

    public function testCanCreateContractorAddress(): void
    {
        $data = [
            'street'      => '123 Main St',
            'city'        => 'Test City',
            'postalCode'  => '12345',
            'country'     => 'US',
            'tenantId'    => $this->tenant->id,
            'building'    => 'Building A',
            'flat'        => '42',
            'description' => 'Main office',
            'type'        => AddressType::REGISTERED_OFFICE->value,
            'isDefault'   => true,
        ];

        $response = $this->postJson("{$this->baseUrl}/{$this->contractor->id}/addresses", $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id', 'street', 'city', 'postalCode', 'country', 'tenantId',
                    'building', 'flat', 'description', 'type', 'isDefault',
                ],
            ])
        ;

        $this->assertDatabaseHas('addresses', [
            'street'           => '123 Main St',
            'city'             => 'Test City',
            'postal_code'      => '12345',
            'is_default'       => true,
            'type'             => AddressType::REGISTERED_OFFICE->value,
            'addressable_id'   => $this->contractor->id,
            'addressable_type' => Contractor::class,
        ]);
    }

    public function testCanShowContractorAddress(): void
    {
        $address = ContractorAddress::factory()->create([
            'tenant_id'        => $this->tenant->id,
            'addressable_id'   => $this->contractor->id,
            'addressable_type' => Contractor::class,
        ]);

        $response = $this->getJson("{$this->baseUrl}/{$this->contractor->id}/addresses/{$address->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'street', 'city', 'postalCode', 'country', 'tenantId',
                    'building', 'flat', 'description', 'type', 'isDefault',
                ],
            ])
        ;
    }

    public function testCannotShowAddressOfDifferentContractor(): void
    {
        $otherContractor = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);
        $address         = ContractorAddress::factory()->create([
            'tenant_id'        => $this->tenant->id,
            'addressable_id'   => $otherContractor->id,
            'addressable_type' => Contractor::class,
        ]);

        $response = $this->getJson("{$this->baseUrl}/{$this->contractor->id}/addresses/{$address->id}");

        $response->assertNotFound();
    }

    public function testCanUpdateContractorAddress(): void
    {
        $address = ContractorAddress::factory()->create([
            'tenant_id'        => $this->tenant->id,
            'addressable_id'   => $this->contractor->id,
            'addressable_type' => Contractor::class,
        ]);

        $data = [
            'street'      => '456 Updated St',
            'city'        => 'Updated City',
            'postalCode'  => '54321',
            'country'     => 'CA',
            'tenantId'    => $this->tenant->id,
            'building'    => 'Building B',
            'flat'        => '24',
            'description' => 'Branch office',
            'type'        => AddressType::CORRESPONDENCE->value,
            'isDefault'   => false,
        ];

        $response = $this->putJson("{$this->baseUrl}/{$this->contractor->id}/addresses/{$address->id}", $data);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'street', 'city', 'postalCode', 'country', 'tenantId',
                    'building', 'flat', 'description', 'type', 'isDefault',
                ],
            ])
        ;

        $this->assertDatabaseHas('addresses', [
            'id'               => $address->id,
            'street'           => '456 Updated St',
            'city'             => 'Updated City',
            'postal_code'      => '54321',
            'is_default'       => false,
            'type'             => AddressType::CORRESPONDENCE->value,
            'addressable_id'   => $this->contractor->id,
            'addressable_type' => Contractor::class,
        ]);
    }

    public function testCannotUpdateAddressOfDifferentContractor(): void
    {
        $otherContractor = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);
        $address         = ContractorAddress::factory()->create([
            'tenant_id'        => $this->tenant->id,
            'addressable_id'   => $otherContractor->id,
            'addressable_type' => Contractor::class,
        ]);

        $data = [
            'street'     => '456 Updated St',
            'city'       => 'Updated City',
            'postalCode' => '54321',
            'country'    => 'CA',
            'tenantId'   => $this->tenant->id,
            'type'       => AddressType::CORRESPONDENCE->value,
        ];

        $response = $this->putJson("{$this->baseUrl}/{$this->contractor->id}/addresses/{$address->id}", $data);

        $response->assertNotFound();
    }

    public function testCanDeleteContractorAddress(): void
    {
        $address = ContractorAddress::factory()->create([
            'tenant_id'        => $this->tenant->id,
            'addressable_id'   => $this->contractor->id,
            'addressable_type' => Contractor::class,
        ]);

        $response = $this->deleteJson("{$this->baseUrl}/{$this->contractor->id}/addresses/{$address->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function testCannotDeleteAddressOfDifferentContractor(): void
    {
        $otherContractor = Contractor::factory()->create(['tenant_id' => $this->tenant->id]);
        $address         = ContractorAddress::factory()->create([
            'tenant_id'        => $this->tenant->id,
            'addressable_id'   => $otherContractor->id,
            'addressable_type' => Contractor::class,
        ]);

        $response = $this->deleteJson("{$this->baseUrl}/{$this->contractor->id}/addresses/{$address->id}");

        $response->assertNotFound();
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }
}
