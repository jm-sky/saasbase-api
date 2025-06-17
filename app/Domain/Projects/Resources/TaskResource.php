<?php

namespace App\Domain\Projects\Resources;

use App\Domain\Common\Resources\UserPreviewResource;
use App\Domain\Projects\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Task
 */
class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Task $this->resource */
        return [
            'id'           => $this->id,
            'tenantId'     => $this->tenant_id,
            'projectId'    => $this->project_id,
            'title'        => $this->title,
            'description'  => $this->description,
            'statusId'     => $this->status_id,
            'priority'     => $this->priority,
            'assigneeId'   => $this->assignee_id,
            'createdById'  => $this->created_by_id,
            'dueDate'      => $this->due_date?->toIso8601String(),
            'createdAt'    => $this->created_at?->toIso8601String(),
            'updatedAt'    => $this->updated_at?->toIso8601String(),
            'deletedAt'    => $this->deleted_at?->toIso8601String(),
            'project'      => $this->whenLoaded('project', fn () => new ProjectResource($this->project)),
            'status'       => $this->whenLoaded('status', fn () => new TaskStatusResource($this->status)),
            'assignee'     => $this->whenLoaded('assignee', fn () => new UserPreviewResource($this->assignee)),
            'createdBy'    => $this->whenLoaded('createdBy', fn () => new UserPreviewResource($this->createdBy)),
        ];
    }
}
