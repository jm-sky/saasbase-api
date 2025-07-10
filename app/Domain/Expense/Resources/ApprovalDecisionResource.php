<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Approval\Models\ApprovalExpenseDecision;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ApprovalExpenseDecision
 */
class ApprovalDecisionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var ApprovalExpenseDecision $this->resource */
        return [
            'id'            => $this->id,
            'executionId'   => $this->execution_id,
            'stepId'        => $this->step_id,
            'approverId'    => $this->approver_id,
            'decision'      => $this->decision->value,
            'decisionLabel' => $this->decision->label(),
            'reason'        => $this->reason,
            'decidedAt'     => $this->decided_at?->toIso8601String(),
            'createdAt'     => $this->created_at?->toIso8601String(),
            'updatedAt'     => $this->updated_at?->toIso8601String(),
            'approver'      => new ApprovalUserResource($this->whenLoaded('approver')),
            'step'          => new ApprovalWorkflowStepResource($this->whenLoaded('step')),
            'isApproval'    => $this->isApproval(),
            'isRejection'   => $this->isRejection(),
        ];
    }
}
