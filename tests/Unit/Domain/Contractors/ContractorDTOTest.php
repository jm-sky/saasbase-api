<?php

namespace Tests\Unit\Domain\Contractors;

use App\Domain\Contractors\DTOs\ContractorDTO;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Tenant\Models\Tenant;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ContractorDTOTest extends TestCase
{
    public function testCanCreateContractorDtoFromModel(): void
    {
        $tenant = Tenant::factory()->create();
        session(['current_tenant_id' => $tenant->id]);

        $contractor = Contractor::factory()->create([
            'name'        => 'Test Contractor',
            'email'       => 'test@example.com',
            'phone'       => '1234567890',
            'country'     => 'US',
            'tax_id'      => '123456789',
            'description' => 'Test description',
            'is_active'   => true,
            'is_buyer'    => true,
            'is_supplier' => false,
        ]);

        $dto = ContractorDTO::from($contractor);

        $this->assertEquals($contractor->id, $dto->id);
        $this->assertEquals($contractor->tenant_id, $dto->tenantId);
        $this->assertEquals($contractor->name, $dto->name);
        $this->assertEquals($contractor->email, $dto->email);
        $this->assertEquals($contractor->phone, $dto->phone);
        $this->assertEquals($contractor->country, $dto->country);
        $this->assertEquals($contractor->tax_id, $dto->taxId);
        $this->assertEquals($contractor->description, $dto->description);
        $this->assertEquals($contractor->is_active, $dto->isActive);
        $this->assertEquals($contractor->is_buyer, $dto->isBuyer);
        $this->assertEquals($contractor->is_supplier, $dto->isSupplier);
    }

    public function testCanConvertContractorDtoToArray(): void
    {
        $dto = new ContractorDTO(
            id: '123e4567-e89b-12d3-a456-426614174000',
            tenantId: '123e4567-e89b-12d3-a456-426614174001',
            name: 'Test Contractor',
            email: 'test@example.com',
            phone: '1234567890',
            country: 'US',
            taxId: '123456789',
            description: 'Test description',
            isActive: true,
            isBuyer: true,
            isSupplier: false,
        );

        $array = $dto->toArray();

        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $array['id']);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174001', $array['tenantId']);
        $this->assertEquals('Test Contractor', $array['name']);
        $this->assertEquals('test@example.com', $array['email']);
        $this->assertEquals('1234567890', $array['phone']);
        $this->assertEquals('US', $array['country']);
        $this->assertEquals('123456789', $array['taxId']);
        $this->assertEquals('Test description', $array['description']);
        $this->assertTrue($array['isActive']);
        $this->assertTrue($array['isBuyer']);
        $this->assertFalse($array['isSupplier']);
    }
}
