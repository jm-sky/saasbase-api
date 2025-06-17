<?php

namespace App\Domain\Projects\Resources;

use App\Domain\Common\Resources\MediaResource;
use App\Domain\Common\Resources\TagResource;
use App\Domain\Common\Resources\UserPreviewResource;
use App\Domain\Projects\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $logoMedia = $this->getFirstMedia('logo');

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
            'logoUrl'        => $logoMedia ? $this->getMediaSignedUrl('logo') : null,
            'logo'           => $logoMedia ? new MediaResource($logoMedia) : null,
            'tags'           => TagResource::collection($this->tags),
            'owner'          => $this->whenLoaded('owner', fn () => new UserPreviewResource($this->owner)),
            'users'          => $this->whenLoaded('users', fn () => UserPreviewResource::collection($this->users)),
            'tasks'          => $this->whenLoaded('tasks', fn () => $this->tasks->toArray()),
            'requiredSkills' => $this->whenLoaded('requiredSkills', fn () => $this->requiredSkills->toArray()),
        ];
    }
}
