<?php

namespace App\Domain\Chat\DTOs;

use App\Domain\Chat\Models\ChatMessage;
use App\Domain\Common\DTOs\BaseDTO;
use Illuminate\Database\Eloquent\Model;

class ChatMessageDTO extends BaseDTO
{
    public function __construct(
        public string $id,
        public string $userId,
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
            $model->content,
            $model->parent_id,
            $model->created_at->toIso8601String(),
            $model->edited_at?->toIso8601String(),
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            $data['id'],
            $data['user_id'] ?? $data['userId'],
            $data['content'],
            $data['parent_id'] ?? $data['parentId'] ?? null,
            $data['created_at'] ?? $data['createdAt'],
            $data['edited_at'] ?? $data['editedAt'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'userId'    => $this->userId,
            'content'   => $this->content,
            'parentId'  => $this->parentId,
            'createdAt' => $this->createdAt,
            'editedAt'  => $this->editedAt,
        ];
    }
}
