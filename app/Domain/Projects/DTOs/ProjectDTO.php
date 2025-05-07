<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Projects\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Project>
 *
 * @property ?string  $id             UUID
 * @property string   $tenantId       UUID
 * @property string   $name
 * @property ?string  $description
 * @property string   $statusId       UUID
 * @property string   $ownerId        UUID
 * @property ?Carbon  $startDate      Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $endDate        Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $createdAt      Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $updatedAt      Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $deletedAt      Internally Carbon, accepts/serializes ISO 8601
 * @property ?UserDTO $owner
 * @property ?array   $users
 * @property ?array   $tasks
 * @property ?array   $requiredSkills
 */
class ProjectDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $tenantId,
        public readonly string $statusId,
        public readonly string $ownerId,
        public readonly ?string $id = null,
        public readonly ?string $description = null,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
        public ?UserDTO $owner = null,
        public ?array $users = null,
        public ?array $tasks = null,
        public ?array $requiredSkills = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var Project $model */
        return new static(
            name: $model->name,
            tenantId: $model->tenant_id,
            statusId: $model->status_id,
            ownerId: $model->owner_id,
            id: $model->id,
            description: $model->description,
            startDate: $model->start_date,
            endDate: $model->end_date,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            owner: $model->relationLoaded('owner') ? UserDTO::fromModel($model->owner) : null,
            users: $model->relationLoaded('users') ? $model->users?->map(fn ($user) => UserDTO::fromModel($user))->toArray() : null,
            tasks: $model->relationLoaded('tasks') ? $model->tasks?->toArray() : null,
            requiredSkills: $model->relationLoaded('requiredSkills') ? $model->requiredSkills?->toArray() : null,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            tenantId: $data['tenant_id'],
            statusId: $data['status_id'],
            ownerId: $data['owner_id'],
            id: $data['id'] ?? null,
            description: $data['description'] ?? null,
            startDate: isset($data['start_date']) ? Carbon::parse($data['start_date']) : null,
            endDate: isset($data['end_date']) ? Carbon::parse($data['end_date']) : null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? Carbon::parse($data['deleted_at']) : null,
            owner: isset($data['owner']) ? UserDTO::fromArray($data['owner']) : null,
            users: isset($data['users']) ? array_map(fn ($user) => UserDTO::fromArray($user), $data['users']) : null,
            tasks: $data['tasks'] ?? null,
            requiredSkills: $data['required_skills'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'tenantId'       => $this->tenantId,
            'name'           => $this->name,
            'description'    => $this->description,
            'statusId'       => $this->statusId,
            'ownerId'        => $this->ownerId,
            'startDate'      => $this->startDate?->toDateString(),
            'endDate'        => $this->endDate?->toDateString(),
            'createdAt'      => $this->createdAt?->toIso8601String(),
            'updatedAt'      => $this->updatedAt?->toIso8601String(),
            'deletedAt'      => $this->deletedAt?->toIso8601String(),
            'owner'          => $this->owner?->toArray(),
            'users'          => $this->users,
            'tasks'          => $this->tasks,
            'requiredSkills' => $this->requiredSkills,
        ];
    }
}
