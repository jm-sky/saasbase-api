<?php

namespace App\Domain\Projects\Resources;

use App\Domain\Auth\Resources\UserResource;
use App\Domain\Projects\Models\ProjectUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var ProjectUser $this->resource */
        return [
            'id'            => $this->id,
            'projectId'     => $this->project_id,
            'userId'        => $this->user_id,
            'projectRoleId' => $this->project_role_id,
            'createdAt'     => $this->created_at?->toIso8601String(),
            'updatedAt'     => $this->updated_at?->toIso8601String(),
            'deletedAt'     => $this->deleted_at?->toIso8601String(),
            'user'          => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'role'          => $this->whenLoaded('role', fn () => new ProjectRoleResource($this->role)),
        ];
    }
}
