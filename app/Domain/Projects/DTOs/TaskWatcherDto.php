<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDto;
use Carbon\Carbon;

class TaskWatcherDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $task_id,
        public readonly string $user_id,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?Carbon $deleted_at,
        public readonly ?UserDto $user = null,
    ) {}

    public static function fromModel(\App\Domain\Projects\Models\TaskWatcher $model, bool $withRelations = false): self
    {
        return new self(
            id: $model->id,
            task_id: $model->task_id,
            user_id: $model->user_id,
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
            'task_id' => $this->task_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'user' => $this->user?->toArray(),
        ];
    }
}
