<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Projects\Models\TaskWatcher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<TaskWatcher>
 *
 * @property ?string  $id        UUID
 * @property string   $taskId
 * @property string   $userId
 * @property ?Carbon  $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $updatedAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $deletedAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?UserDTO $user
 */
class TaskWatcherDTO extends BaseDTO
{
    public function __construct(
        public readonly string $taskId,
        public readonly string $userId,
        public readonly ?string $id = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
        public ?UserDTO $user = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var TaskWatcher $model */
        return new static(
            taskId: $model->task_id,
            userId: $model->user_id,
            id: $model->id,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            user: $model->user ? UserDTO::fromModel($model->user) : null,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            taskId: $data['task_id'],
            userId: $data['user_id'],
            id: $data['id'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? Carbon::parse($data['deleted_at']) : null,
            user: isset($data['user']) ? UserDTO::fromArray($data['user']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'taskId'    => $this->taskId,
            'userId'    => $this->userId,
            'createdAt' => $this->createdAt?->toIso8601String(),
            'updatedAt' => $this->updatedAt?->toIso8601String(),
            'deletedAt' => $this->deletedAt?->toIso8601String(),
            'user'      => $this->user?->toArray(),
        ];
    }
}
