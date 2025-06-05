<?php

namespace App\Domain\Chat\DTOs;

use App\Domain\Chat\Models\ChatMessage;
use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Users\DTOs\UserPreviewDTO;
use Illuminate\Database\Eloquent\Model;

class ChatMessageDTO extends BaseDTO
{
    public function __construct(
        public string $id,
        public ?string $tempId,
        public string $userId,
        public UserPreviewDTO $user,
        public string $content,
        public string $role,
        public bool $isAi,
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
            $model->temp_id,
            $model->user_id,
            user: UserPreviewDTO::fromModel($model->user),
            content: $model->content,
            role: $model->role,
            isAi: $model->is_ai,
            parentId: $model->parent_id,
            createdAt: $model->created_at->toIso8601String(),
            editedAt: $model->edited_at?->toIso8601String(),
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            $data['id'],
            $data['temp_id'] ?? $data['tempId'] ?? null,
            $data['user_id'] ?? $data['userId'],
            user: UserPreviewDTO::fromArray($data['user']),
            content: $data['content'],
            role: $data['role'] ?? 'user',
            isAi: $data['is_ai'] ?? $data['isAi'] ?? false,
            parentId: $data['parent_id'] ?? $data['parentId'] ?? null,
            createdAt: $data['created_at'] ?? $data['createdAt'],
            editedAt: $data['edited_at'] ?? $data['editedAt'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'tempId'    => $this->tempId,
            'userId'    => $this->userId,
            'user'      => $this->user->toArray(),
            'content'   => $this->content,
            'role'      => $this->role,
            'isAi'      => $this->isAi,
            'parentId'  => $this->parentId,
            'createdAt' => $this->createdAt,
            'editedAt'  => $this->editedAt,
        ];
    }
}
