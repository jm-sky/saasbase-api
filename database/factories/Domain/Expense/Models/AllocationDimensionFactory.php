<?php

namespace Database\Factories\Domain\Expense\Models;

use App\Domain\Expense\Enums\AllocationDimensionType;
use App\Domain\Expense\Models\AllocationDimension;
use App\Domain\Expense\Models\ExpenseAllocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Expense\Models\AllocationDimension>
 */
class AllocationDimensionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = AllocationDimension::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'allocation_id'  => ExpenseAllocation::factory(),
            'dimension_type' => $this->faker->randomElement(AllocationDimensionType::cases()),
            'dimension_id'   => $this->faker->uuid(),
        ];
    }

    /**
     * State for specific dimension type.
     */
    public function ofType(AllocationDimensionType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'dimension_type' => $type,
        ]);
    }

    /**
     * State for project dimension.
     */
    public function project(): static
    {
        return $this->ofType(AllocationDimensionType::PROJECT);
    }

    /**
     * State for transaction type dimension.
     */
    public function transactionType(): static
    {
        return $this->ofType(AllocationDimensionType::TRANSACTION_TYPE);
    }

    /**
     * State for cost type dimension.
     */
    public function costType(): static
    {
        return $this->ofType(AllocationDimensionType::COST_TYPE);
    }

    /**
     * State for structure dimension.
     */
    public function structure(): static
    {
        return $this->ofType(AllocationDimensionType::STRUCTURE);
    }

    /**
     * State for employees dimension.
     */
    public function employees(): static
    {
        return $this->ofType(AllocationDimensionType::EMPLOYEES);
    }
}
