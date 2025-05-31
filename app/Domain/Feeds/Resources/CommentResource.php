<?php

namespace App\Domain\Feeds\Resources;

use App\Domain\Common\Resources\UserPreviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'feedId'    => $this->feed_id,
            'userId'    => $this->user_id,
            'content'   => $this->content,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'user'      => new UserPreviewResource($this->whenLoaded('user')),
        ];
    }
}
