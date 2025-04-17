<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\DTOs\TaskDTO;
use App\Domain\Projects\DTOs\ProjectRequiredSkillDTO;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $tenantId
 * @property string $name
 * @property ?string $description
 * @property string $status
 * @property string $startDate
 * @property ?string $endDate
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 * @property ?UserDTO $owner
 * @property ?array $users
 * @property ?array $tasks
 * @property ?array $requiredSkills
 */
class ProjectDTO extends Data
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $name,
        public readonly string $status,
        public readonly string $startDate,
        public readonly ?string $id = null,
        public readonly ?string $description = null,
        public readonly ?string $endDate = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
        public ?UserDTO $owner = null,
        public ?array $users = null,
        public ?array $tasks = null,
        public ?array $requiredSkills = null,
    ) {}

    public static function fromModel(Project $model): self
    {
        return new self(
            tenantId: $model->tenant_id,
            name: $model->name,
            status: $model->status,
            startDate: $model->start_date->format('Y-m-d'),
            id: $model->id,
            description: $model->description,
            endDate: $model->end_date?->format('Y-m-d'),
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
            owner: $model->owner ? UserDTO::fromModel($model->owner) : null,
            users: $model->users ? $model->users->map(fn ($user) => UserDTO::fromModel($user))->toArray() : null,
            tasks: $model->tasks ? $model->tasks->map(fn ($task) => TaskDTO::fromModel($task))->toArray() : null,
            requiredSkills: $model->requiredSkills ? $model->requiredSkills->map(fn ($skill) => ProjectRequiredSkillDTO::fromModel($skill))->toArray() : null,
        );
    }
}
