<?php

namespace Database\Factories\Domain\Expense\Models;

use App\Domain\Expense\Enums\ExpenseAllocationStatus;
use App\Domain\Expense\Models\Expense;
use App\Domain\Expense\Models\ExpenseAllocation;
use App\Domain\Tenant\Models\Tenant;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Expense\Models\ExpenseAllocation>
 */
class ExpenseAllocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ExpenseAllocation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id'  => Tenant::factory(),
            'expense_id' => Expense::factory(),
            'amount'     => BigDecimal::of($this->faker->randomFloat(2, 10, 1000)),
            'note'       => $this->faker->optional()->sentence(),
            'status'     => ExpenseAllocationStatus::PENDING,
        ];
    }

    /**
     * State for allocation with specific amount.
     */
    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => BigDecimal::of((string) $amount),
        ]);
    }

    /**
     * State for approved allocation.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExpenseAllocationStatus::APPROVED,
        ]);
    }

    /**
     * State for rejected allocation.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExpenseAllocationStatus::REJECTED,
        ]);
    }

    /**
     * State for allocated status.
     */
    public function allocated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExpenseAllocationStatus::ALLOCATED,
        ]);
    }
}
