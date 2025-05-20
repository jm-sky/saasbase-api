<?php

namespace App\Domain\Projects\Resources;

use App\Domain\Auth\Resources\UserResource;
use App\Domain\Projects\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Project $this->resource */
        return [
            'id'             => $this->id,
            'tenantId'       => $this->tenant_id,
            'name'           => $this->name,
            'description'    => $this->description,
            'statusId'       => $this->status_id,
            'ownerId'        => $this->owner_id,
            'startDate'      => $this->start_date?->toIso8601String(),
            'endDate'        => $this->end_date?->toIso8601String(),
            'createdAt'      => $this->created_at?->toIso8601String(),
            'updatedAt'      => $this->updated_at?->toIso8601String(),
            'deletedAt'      => $this->deleted_at?->toIso8601String(),
            'owner'          => $this->whenLoaded('owner', fn () => new UserResource($this->owner)),
            'users'          => $this->whenLoaded('users', fn () => UserResource::collection($this->users)),
            'tasks'          => $this->whenLoaded('tasks', fn () => $this->tasks->toArray()),
            'requiredSkills' => $this->whenLoaded('requiredSkills', fn () => $this->requiredSkills->toArray()),
        ];
    }
}
