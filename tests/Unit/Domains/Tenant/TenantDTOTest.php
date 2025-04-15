<?php

namespace Tests\Unit\Domains\Tenant;

use App\Domains\Tenant\DTOs\TenantDTO;
use App\Domains\Tenant\Models\Tenant;
use Tests\TestCase;

class TenantDTOTest extends TestCase
{
    public function test_can_create_tenant_dto(): void
    {
        $data = [
            'id' => fake()->uuid(),
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
            'deleted_at' => null
        ];

        $dto = TenantDTO::from($data);

        $this->assertEquals($data['id'], $dto->id);
        $this->assertEquals($data['name'], $dto->name);
        $this->assertEquals($data['slug'], $dto->slug);
        $this->assertEquals($data['created_at'], $dto->createdAt);
        $this->assertEquals($data['updated_at'], $dto->updatedAt);
        $this->assertEquals($data['deleted_at'], $dto->deletedAt);
    }

    public function test_can_create_tenant_dto_with_minimal_data(): void
    {
        $data = [
            'name' => 'Test Tenant',
            'slug' => 'test-tenant'
        ];

        $dto = TenantDTO::from($data);

        $this->assertNull($dto->id);
        $this->assertEquals($data['name'], $dto->name);
        $this->assertEquals($data['slug'], $dto->slug);
        $this->assertNull($dto->createdAt);
        $this->assertNull($dto->updatedAt);
        $this->assertNull($dto->deletedAt);
    }

    public function test_can_collect_multiple_tenant_dtos(): void
    {
        $data = [
            [
                'id' => fake()->uuid(),
                'name' => 'Test Tenant 1',
                'slug' => 'test-tenant-1'
            ],
            [
                'id' => fake()->uuid(),
                'name' => 'Test Tenant 2',
                'slug' => 'test-tenant-2'
            ]
        ];

        $dtos = TenantDTO::collect($data);

        $this->assertCount(2, $dtos);
        $this->assertEquals($data[0]['name'], $dtos[0]->name);
        $this->assertEquals($data[1]['name'], $dtos[1]->name);
    }

    public function test_can_create_tenant_dto_from_model(): void
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
        ]);

        $dto = TenantDTO::from($tenant);

        $this->assertEquals($tenant->id, $dto->id);
        $this->assertEquals($tenant->name, $dto->name);
        $this->assertEquals($tenant->slug, $dto->slug);
        $this->assertEquals($tenant->created_at, $dto->createdAt);
        $this->assertEquals($tenant->updated_at, $dto->updatedAt);
        $this->assertEquals($tenant->deleted_at, $dto->deletedAt);
    }

    public function test_can_convert_tenant_dto_to_array(): void
    {
        $dto = new TenantDTO(
            id: '123e4567-e89b-12d3-a456-426614174000',
            name: 'Test Tenant',
            slug: 'test-tenant',
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
            deletedAt: null,
        );

        $array = $dto->toArray();

        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $array['id']);
        $this->assertEquals('Test Tenant', $array['name']);
        $this->assertEquals('test-tenant', $array['slug']);
        $this->assertEquals('2024-01-01 00:00:00', $array['createdAt']);
        $this->assertEquals('2024-01-01 00:00:00', $array['updatedAt']);
        $this->assertNull($array['deletedAt']);
    }
}
