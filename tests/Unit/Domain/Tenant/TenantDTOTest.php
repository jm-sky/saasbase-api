<?php

namespace Tests\Unit\Domain\Tenant;

use Carbon\Carbon;
use Tests\TestCase;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\DTOs\TenantDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantDTOTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_tenant_dto(): void
    {
        $data = [
            'id' => fake()->uuid(),
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'createdAt' => now()->toIso8601String(),
            'updatedAt' => now()->toIso8601String(),
            'deletedAt' => null
        ];

        $dto = TenantDTO::from($data);

        $this->assertEquals($data['id'], $dto->id);
        $this->assertEquals($data['name'], $dto->name);
        $this->assertEquals($data['slug'], $dto->slug);
        $this->assertEquals($data['createdAt'], $dto->createdAt->toIso8601String());
        $this->assertEquals($data['updatedAt'], $dto->updatedAt->toIso8601String());
        $this->assertEquals($data['deletedAt'], $dto->deletedAt);
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
            createdAt: Carbon::parse('2024-01-01 00:00:00'),
            updatedAt: Carbon::parse('2024-01-01 00:00:00'),
            deletedAt: null,
        );

        $array = $dto->toArray();

        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $array['id']);
        $this->assertEquals('Test Tenant', $array['name']);
        $this->assertEquals('test-tenant', $array['slug']);
        $this->assertEquals('2024-01-01T00:00:00+00:00', $array['createdAt']);
        $this->assertEquals('2024-01-01T00:00:00+00:00', $array['updatedAt']);
        $this->assertNull($array['deletedAt']);
    }
}
