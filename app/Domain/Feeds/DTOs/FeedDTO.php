<?php

namespace App\Domain\Feeds\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Feeds\Models\Feed;
use App\Domain\Users\DTOs\PublicUserDTO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Feed>
 *
 * @property ?string $id
 * @property string  $tenantId
 * @property string  $userId
 * @property string  $title
 * @property string  $content
 * @property ?string $contentHtml
 * @property ?Carbon $createdAt
 * @property ?Carbon $updatedAt
 * @property ?Carbon $deletedAt
 * @property ?array  $user
 * @property ?int    $commentsCount
 */
class FeedDTO extends BaseDTO
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $userId,
        public readonly string $title,
        public readonly string $content,
        public readonly ?string $id = null,
        public readonly ?string $contentHtml = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
        public readonly ?PublicUserDTO $user = null,
        public readonly ?int $commentsCount = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            tenantId: $data['tenant_id'],
            userId: $data['user_id'],
            title: $data['title'],
            content: $data['content'],
            id: $data['id'] ?? null,
            contentHtml: $data['content_html'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? Carbon::parse($data['deleted_at']) : null,
            user: $data['user'] ?? null,
            commentsCount: $data['comments_count'] ?? null,
        );
    }

    public static function fromModel(Model $model): static
    {
        if (!$model instanceof Feed) {
            throw new \InvalidArgumentException('Model must be instance of Feed');
        }

        $user = null;

        if ($model->relationLoaded('user') && $model->user) {
            $user = PublicUserDTO::fromModel($model->user);
        }

        return new self(
            tenantId: $model->tenant_id,
            userId: $model->user_id,
            title: $model->title,
            content: $model->content,
            id: $model->id,
            contentHtml: $model->content_html,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            user: $user,
            commentsCount: $model->comments_count ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'tenantId'      => $this->tenantId,
            'userId'        => $this->userId,
            'title'         => $this->title,
            'content'       => $this->content,
            'contentHtml'   => $this->contentHtml,
            'createdAt'     => $this->createdAt?->toIso8601String(),
            'updatedAt'     => $this->updatedAt?->toIso8601String(),
            'deletedAt'     => $this->deletedAt?->toIso8601String(),
            'user'          => $this->user?->toArray(),
            'commentsCount' => $this->commentsCount,
        ];
    }
}
