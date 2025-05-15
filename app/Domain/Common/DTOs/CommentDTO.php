<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Models\Comment;
use App\Domain\Users\DTOs\PublicUserDTO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Comment>
 *
 * @property string         $userId
 * @property string         $content
 * @property string         $commentableId
 * @property string         $commentableType
 * @property ?string        $id              UUID
 * @property ?array         $meta            (canEdit, canDelete)
 * @property ?Carbon        $createdAt       Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon        $updatedAt       Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon        $deletedAt       Internally Carbon, accepts/serializes ISO 8601
 * @property ?PublicUserDTO $user
 */
class CommentDTO extends BaseDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $content,
        public readonly string $commentableId,
        public readonly string $commentableType,
        public readonly ?string $id = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
        public ?PublicUserDTO $user = null,
        public ?array $meta = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var Comment $model */
        return new static(
            userId: $model->user_id,
            content: $model->content,
            commentableId: $model->commentable_id,
            commentableType: $model->commentable_type,
            id: $model->id,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            user: $model->user ? PublicUserDTO::fromModel($model->user) : null,
            meta: [
                'canEdit'   => $model->canEdit(),
                'canDelete' => $model->canDelete(),
            ],
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            userId: $data['user_id'],
            content: $data['content'],
            commentableId: $data['commentable_id'],
            commentableType: $data['commentable_type'],
            id: $data['id'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? Carbon::parse($data['deleted_at']) : null,
            user: isset($data['user']) ? PublicUserDTO::fromArray($data['user']) : null,
            meta: isset($data['meta']) ? $data['meta'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'userId'          => $this->userId,
            'content'         => $this->content,
            'commentableId'   => $this->commentableId,
            'commentableType' => $this->commentableType,
            'createdAt'       => $this->createdAt?->toIso8601String(),
            'updatedAt'       => $this->updatedAt?->toIso8601String(),
            'deletedAt'       => $this->deletedAt?->toIso8601String(),
            'user'            => $this->user?->toArray(),
            'meta'            => $this->meta,
        ];
    }
}
