<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Approval\Models\ApprovalStepApprover;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ApprovalStepApprover
 */
class ApprovalStepApproverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var ApprovalStepApprover $this->resource */
        return [
            'id'                         => $this->id,
            'stepId'                     => $this->step_id,
            'approverType'               => $this->approver_type->value,
            'approverTypeLabel'          => $this->approver_type->label(),
            'approverValue'              => $this->approver_value,
            'organizationUnitId'         => $this->organization_unit_id,
            'canDelegate'                => $this->can_delegate,
            'createdAt'                  => $this->created_at?->toIso8601String(),
            'updatedAt'                  => $this->updated_at?->toIso8601String(),
            'isUserApprover'             => $this->isUserApprover(),
            'isUnitRoleApprover'         => $this->isUnitRoleApprover(),
            'isSystemPermissionApprover' => $this->isSystemPermissionApprover(),
        ];
    }
}
