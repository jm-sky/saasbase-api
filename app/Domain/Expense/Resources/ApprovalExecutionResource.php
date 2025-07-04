<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Approval\Models\ApprovalExpenseExecution;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ApprovalExpenseExecution
 */
class ApprovalExecutionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var ApprovalExpenseExecution $this->resource */
        return [
            'id'              => $this->id,
            'expenseId'       => $this->expense_id,
            'workflowId'      => $this->workflow_id,
            'currentStepId'   => $this->current_step_id,
            'status'          => $this->status->value,
            'statusLabel'     => $this->status->label(),
            'initiatorId'     => $this->initiator_id,
            'startedAt'       => $this->started_at?->toIso8601String(),
            'completedAt'     => $this->completed_at?->toIso8601String(),
            'durationSeconds' => $this->getDurationInSeconds(),
            'createdAt'       => $this->created_at?->toIso8601String(),
            'updatedAt'       => $this->updated_at?->toIso8601String(),
            // Relationships
            'expense'         => new ExpenseResource($this->whenLoaded('expense')),
            'workflow'        => new ApprovalWorkflowResource($this->whenLoaded('workflow')),
            'currentStep'     => new ApprovalWorkflowStepResource($this->whenLoaded('currentStep')),
            'initiator'       => new ApprovalUserResource($this->whenLoaded('initiator')),
            'decisions'       => ApprovalDecisionResource::collection($this->whenLoaded('decisions')),
            // Status checks
            'isPending'       => $this->isPending(),
            'isComplete'      => $this->isComplete(),
            'isApproved'      => $this->isApproved(),
            'isRejected'      => $this->isRejected(),
        ];
    }
}
