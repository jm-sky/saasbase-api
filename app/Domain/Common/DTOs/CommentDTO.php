<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Common\Models\Comment;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $userId
 * @property string $content
 * @property string $commentableId
 * @property string $commentableType
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 * @property ?UserDTO $user
 */
class CommentDTO extends Data
{
    public function __construct(
        public readonly string $userId,
        public readonly string $content,
        public readonly string $commentableId,
        public readonly string $commentableType,
        public readonly ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
        public ?UserDTO $user = null,
    ) {}

    public static function fromModel(Comment $model): self
    {
        return new self(
            userId: $model->user_id,
            content: $model->content,
            commentableId: $model->commentable_id,
            commentableType: $model->commentable_type,
            id: $model->id,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
            user: $model->user ? UserDTO::fromModel($model->user) : null,
        );
    }
}
