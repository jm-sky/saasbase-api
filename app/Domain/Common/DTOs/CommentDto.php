<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Auth\DTOs\UserDto;
use Carbon\Carbon;

class CommentDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $user_id,
        public readonly string $content,
        public readonly string $commentable_id,
        public readonly string $commentable_type,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?Carbon $deleted_at,
        public readonly ?UserDto $user = null,
    ) {}

    public static function fromModel(\App\Domain\Common\Models\Comment $model, bool $withRelations = false): self
    {
        return new self(
            id: $model->id,
            user_id: $model->user_id,
            content: $model->content,
            commentable_id: $model->commentable_id,
            commentable_type: $model->commentable_type,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
            deleted_at: $model->deleted_at,
            user: $withRelations && $model->relationLoaded('user')
                ? UserDto::fromModel($model->user)
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'content' => $this->content,
            'commentable_id' => $this->commentable_id,
            'commentable_type' => $this->commentable_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'user' => $this->user?->toArray(),
        ];
    }
}
