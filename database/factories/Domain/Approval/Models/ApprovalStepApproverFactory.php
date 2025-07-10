<?php

namespace Database\Factories\Domain\Approval\Models;

use App\Domain\Approval\Enums\ApproverType;
use App\Domain\Approval\Models\ApprovalStepApprover;
use App\Domain\Approval\Models\ApprovalWorkflowStep;
use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Approval\Models\ApprovalStepApprover>
 */
class ApprovalStepApproverFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ApprovalStepApprover::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'step_id'                => ApprovalWorkflowStep::factory(),
            'approver_type'          => ApproverType::USER,
            'approver_value'         => User::factory(),
            'organization_unit_id'   => null,
            'can_delegate'           => false,
        ];
    }

    /**
     * State for user approver.
     */
    public function userApprover(?string $userId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'approver_type'  => ApproverType::USER,
            'approver_value' => $userId ?? User::factory(),
        ]);
    }

    /**
     * State for unit role approver.
     */
    public function unitRoleApprover(string $roleLevel, ?string $organizationUnitId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'approver_type'        => ApproverType::UNIT_ROLE,
            'approver_value'       => $roleLevel,
            'organization_unit_id' => $organizationUnitId,
        ]);
    }

    /**
     * State for system permission approver.
     */
    public function systemPermissionApprover(string $permission): static
    {
        return $this->state(fn (array $attributes) => [
            'approver_type'        => ApproverType::SYSTEM_PERMISSION,
            'approver_value'       => $permission,
            'organization_unit_id' => null,
        ]);
    }

    /**
     * State for approver that can delegate.
     */
    public function canDelegate(): static
    {
        return $this->state(fn (array $attributes) => [
            'can_delegate' => true,
        ]);
    }
}
