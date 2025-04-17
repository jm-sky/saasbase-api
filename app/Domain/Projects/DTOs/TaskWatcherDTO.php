<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Projects\Models\TaskWatcher;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $taskId
 * @property string $userId
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 * @property ?UserDTO $user
 */
class TaskWatcherDTO extends Data
{
    public function __construct(
        public readonly string $taskId,
        public readonly string $userId,
        public readonly ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
        public ?UserDTO $user = null,
    ) {}

    public static function fromModel(TaskWatcher $model): self
    {
        return new self(
            taskId: $model->task_id,
            userId: $model->user_id,
            id: $model->id,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
            user: $model->user ? UserDTO::fromModel($model->user) : null,
        );
    }
}
