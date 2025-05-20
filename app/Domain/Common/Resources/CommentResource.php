<?php

namespace App\Domain\Common\Resources;

use App\Domain\Common\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Comment $this->resource */
        return [
            'id'              => $this->id,
            'userId'          => $this->user_id,
            'content'         => $this->content,
            'commentableId'   => $this->commentable_id,
            'commentableType' => $this->commentable_type,
            'meta'            => [
                'canEdit'   => $this->canEdit(),
                'canDelete' => $this->canDelete(),
            ],
            'user'      => $this->whenLoaded('user', fn () => new UserPreviewResource($this->user)),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'deletedAt' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
