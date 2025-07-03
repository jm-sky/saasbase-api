<?php

namespace Database\Factories\Domain\Approval\Models;

use App\Domain\Approval\Models\ApprovalWorkflow;
use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Approval\Models\ApprovalWorkflow>
 */
class ApprovalWorkflowFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ApprovalWorkflow::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id'        => Tenant::factory(),
            'name'             => $this->faker->words(3, true),
            'description'      => $this->faker->optional()->sentence(),
            'match_amount_min' => null,
            'match_amount_max' => null,
            'match_conditions' => null,
            'priority'         => $this->faker->numberBetween(0, 10),
            'is_active'        => true,
            'created_by'       => User::factory(),
        ];
    }

    /**
     * State for workflow with amount range.
     */
    public function withAmountRange(float $min, float $max): static
    {
        return $this->state(fn (array $attributes) => [
            'match_amount_min' => BigDecimal::of((string) $min),
            'match_amount_max' => BigDecimal::of((string) $max),
        ]);
    }

    /**
     * State for workflow with minimum amount only.
     */
    public function withMinAmount(float $min): static
    {
        return $this->state(fn (array $attributes) => [
            'match_amount_min' => BigDecimal::of((string) $min),
            'match_amount_max' => null,
        ]);
    }

    /**
     * State for workflow with maximum amount only.
     */
    public function withMaxAmount(float $max): static
    {
        return $this->state(fn (array $attributes) => [
            'match_amount_min' => null,
            'match_amount_max' => BigDecimal::of((string) $max),
        ]);
    }

    /**
     * State for workflow with allocation conditions.
     */
    public function withConditions(array $conditions): static
    {
        return $this->state(fn (array $attributes) => [
            'match_conditions' => $conditions,
        ]);
    }

    /**
     * State for inactive workflow.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * State for high priority workflow.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 10,
        ]);
    }

    /**
     * State for low priority workflow.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 1,
        ]);
    }
}
