<?php

namespace App\Domain\Chat\DTOs;

use App\Domain\Chat\Models\ChatMessage;
use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Users\DTOs\PublicUserDTO;
use Illuminate\Database\Eloquent\Model;

class ChatMessageDTO extends BaseDTO
{
    public function __construct(
        public string $id,
        public string $userId,
        public PublicUserDTO $user,
        public string $content,
        public ?string $parentId,
        public string $createdAt,
        public ?string $editedAt,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        if (!$model instanceof ChatMessage) {
            throw new \InvalidArgumentException('Model must be instance of ChatMessage');
        }

        return new static(
            $model->id,
            $model->user_id,
            user: PublicUserDTO::fromModel($model->user),
            content: $model->content,
            parentId: $model->parent_id,
            createdAt: $model->created_at->toIso8601String(),
            editedAt: $model->edited_at?->toIso8601String(),
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            $data['id'],
            $data['user_id'] ?? $data['userId'],
            user: PublicUserDTO::fromArray($data['user']),
            content: $data['content'],
            parentId: $data['parent_id'] ?? $data['parentId'] ?? null,
            createdAt: $data['created_at'] ?? $data['createdAt'],
            editedAt: $data['edited_at'] ?? $data['editedAt'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'userId'    => $this->userId,
            'user'      => $this->user->toArray(),
            'content'   => $this->content,
            'parentId'  => $this->parentId,
            'createdAt' => $this->createdAt,
            'editedAt'  => $this->editedAt,
        ];
    }
}
