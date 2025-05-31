<?php

namespace App\Domain\Feeds\Resources;

use App\Domain\Common\Resources\UserPreviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'tenantId'      => $this->tenant_id,
            'userId'        => $this->user_id,
            'title'         => $this->title,
            'content'       => $this->content,
            'createdAt'     => $this->created_at,
            'updatedAt'     => $this->updated_at,
            'creator'       => new UserPreviewResource($this->whenLoaded('user')),
            'commentsCount' => $this->whenCounted('comments'),
            'comments'      => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
