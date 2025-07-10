<?php

namespace Database\Factories\Domain\Approval\Models;

use App\Domain\Approval\Models\ApprovalWorkflow;
use App\Domain\Approval\Models\ApprovalWorkflowStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Approval\Models\ApprovalWorkflowStep>
 */
class ApprovalWorkflowStepFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ApprovalWorkflowStep::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workflow_id'           => ApprovalWorkflow::factory(),
            'step_order'            => $this->faker->numberBetween(1, 5),
            'name'                  => $this->faker->words(3, true),
            'require_all_approvers' => false,
            'min_approvers'         => 1,
        ];
    }

    /**
     * State for step requiring all approvers.
     */
    public function requireAllApprovers(): static
    {
        return $this->state(fn (array $attributes) => [
            'require_all_approvers' => true,
        ]);
    }

    /**
     * State for step with specific minimum approvers.
     */
    public function withMinApprovers(int $minApprovers): static
    {
        return $this->state(fn (array $attributes) => [
            'min_approvers' => $minApprovers,
        ]);
    }

    /**
     * State for first step in workflow.
     */
    public function firstStep(): static
    {
        return $this->state(fn (array $attributes) => [
            'step_order' => 1,
        ]);
    }

    /**
     * State for step with specific order.
     */
    public function withOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'step_order' => $order,
        ]);
    }
}
