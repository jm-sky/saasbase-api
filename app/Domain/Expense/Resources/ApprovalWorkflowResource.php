<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Approval\Models\ApprovalWorkflow;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ApprovalWorkflow
 */
class ApprovalWorkflowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var ApprovalWorkflow $this->resource */
        return [
            'id'                => $this->id,
            'tenantId'          => $this->tenant_id,
            'name'              => $this->name,
            'description'       => $this->description,
            'priority'          => $this->priority,
            'isActive'          => $this->is_active,
            'matchAmountMin'    => $this->match_amount_min?->toFloat(),
            'matchAmountMax'    => $this->match_amount_max?->toFloat(),
            'createdAt'         => $this->created_at?->toIso8601String(),
            'updatedAt'         => $this->updated_at?->toIso8601String(),
            'steps'             => ApprovalWorkflowStepResource::collection($this->whenLoaded('steps')),
        ];
    }
}
