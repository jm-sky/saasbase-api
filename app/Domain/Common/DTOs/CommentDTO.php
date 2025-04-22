<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Common\Models\Comment;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string  $id              UUID
 * @property string   $userId
 * @property string   $content
 * @property string   $commentableId
 * @property string   $commentableType
 * @property ?Carbon  $createdAt       Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $updatedAt       Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $deletedAt       Internally Carbon, accepts/serializes ISO 8601
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
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $deletedAt = null,
        public ?UserDTO $user = null,
    ) {
    }

    public static function fromModel(Comment $model): self
    {
        return new self(
            userId: $model->user_id,
            content: $model->content,
            commentableId: $model->commentable_id,
            commentableType: $model->commentable_type,
            id: $model->id,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            user: $model->user ? UserDTO::fromModel($model->user) : null,
        );
    }
}
