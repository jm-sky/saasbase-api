<?php

namespace Database\Factories\Domain\Financial\Models;

use App\Domain\Financial\Models\AllocationTransactionType;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Financial\Models\AllocationTransactionType>
 */
class AllocationTransactionTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = AllocationTransactionType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id'   => $this->faker->optional()->randomElement([null, Tenant::factory()]),
            'code'        => $this->faker->unique()->lexify('???_???'),
            'name'        => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'is_active'   => true,
        ];
    }

    /**
     * State for global transaction type (no tenant).
     */
    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => null,
        ]);
    }

    /**
     * State for tenant-specific transaction type.
     */
    public function forTenant(string $tenantId): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenantId,
        ]);
    }

    /**
     * State for inactive transaction type.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
