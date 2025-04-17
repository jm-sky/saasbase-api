<?php

namespace Tests\Unit\Domain\Contractors;

use App\Domain\Contractors\DTOs\ContractorDTO;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Tenant\Models\Tenant;
use Tests\TestCase;

class ContractorDTOTest extends TestCase
{
    public function test_can_create_contractor_dto_from_model(): void
    {
        $tenant = Tenant::factory()->create();
        $contractor = Contractor::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Contractor',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip_code' => '12345',
            'country' => 'US',
            'tax_id' => '123456789',
            'notes' => 'Test notes',
            'is_active' => true,
        ]);

        $dto = ContractorDTO::from($contractor);

        $this->assertEquals($contractor->id, $dto->id);
        $this->assertEquals($contractor->tenant_id, $dto->tenantId);
        $this->assertEquals($contractor->name, $dto->name);
        $this->assertEquals($contractor->email, $dto->email);
        $this->assertEquals($contractor->phone, $dto->phone);
        $this->assertEquals($contractor->address, $dto->address);
        $this->assertEquals($contractor->city, $dto->city);
        $this->assertEquals($contractor->state, $dto->state);
        $this->assertEquals($contractor->zip_code, $dto->zipCode);
        $this->assertEquals($contractor->country, $dto->country);
        $this->assertEquals($contractor->tax_id, $dto->taxId);
        $this->assertEquals($contractor->notes, $dto->notes);
        $this->assertEquals($contractor->is_active, $dto->isActive);
    }

    public function test_can_convert_contractor_dto_to_array(): void
    {
        $dto = new ContractorDTO(
            id: '123e4567-e89b-12d3-a456-426614174000',
            tenantId: '123e4567-e89b-12d3-a456-426614174001',
            name: 'Test Contractor',
            email: 'test@example.com',
            phone: '1234567890',
            address: '123 Test St',
            city: 'Test City',
            state: 'TS',
            zipCode: '12345',
            country: 'US',
            taxId: '123456789',
            notes: 'Test notes',
            isActive: true,
        );

        $array = $dto->toArray();

        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $array['id']);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174001', $array['tenantId']);
        $this->assertEquals('Test Contractor', $array['name']);
        $this->assertEquals('test@example.com', $array['email']);
        $this->assertEquals('1234567890', $array['phone']);
        $this->assertEquals('123 Test St', $array['address']);
        $this->assertEquals('Test City', $array['city']);
        $this->assertEquals('TS', $array['state']);
        $this->assertEquals('12345', $array['zipCode']);
        $this->assertEquals('US', $array['country']);
        $this->assertEquals('123456789', $array['taxId']);
        $this->assertEquals('Test notes', $array['notes']);
        $this->assertTrue($array['isActive']);
    }
}
