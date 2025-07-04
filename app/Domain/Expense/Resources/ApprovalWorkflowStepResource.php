<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Approval\Models\ApprovalWorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ApprovalWorkflowStep
 */
class ApprovalWorkflowStepResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var ApprovalWorkflowStep $this->resource */
        return [
            'id'                   => $this->id,
            'workflowId'           => $this->workflow_id,
            'stepOrder'            => $this->step_order,
            'name'                 => $this->name,
            'requireAllApprovers'  => $this->require_all_approvers,
            'minApprovers'         => $this->min_approvers,
            'createdAt'            => $this->created_at?->toIso8601String(),
            'updatedAt'            => $this->updated_at?->toIso8601String(),
            'approvers'            => ApprovalStepApproverResource::collection($this->whenLoaded('approvers')),
            'isFirstStep'          => $this->isFirstStep(),
            'isLastStep'           => $this->isLastStep(),
        ];
    }
}
