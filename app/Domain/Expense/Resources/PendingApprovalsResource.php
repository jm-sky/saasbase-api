<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Approval\Models\ApprovalExpenseExecution;
use Illuminate\Http\Request;

/**
 * Resource for pending approvals with additional context.
 *
 * @mixin ApprovalExpenseExecution
 */
class PendingApprovalsResource extends ApprovalExecutionResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $baseData = parent::toArray($request);

        /* @var ApprovalExpenseExecution $this->resource */
        return array_merge($baseData, [
            // Additional pending-specific data
            'waitingTime'     => $this->started_at ? now()->diffInHours($this->started_at) : null,
            'priorityLevel'   => $this->whenLoaded('workflow', fn () => $this->workflow->priority),
            'currentStepName' => $this->whenLoaded('currentStep', fn () => $this->currentStep->name),
            'expenseAmount'   => $this->whenLoaded('expense', fn () => $this->expense->total_gross->toFloat()),
            'expenseNumber'   => $this->whenLoaded('expense', fn () => $this->expense->number),
            'expenseCreator'  => $this->whenLoaded('expense.createdByUser', function () {
                return [
                    'id'       => $this->expense->createdByUser->id,
                    'name'     => $this->expense->createdByUser->full_name,
                    'email'    => $this->expense->createdByUser->email,
                ];
            }),
        ]);
    }
}
