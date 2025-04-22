<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Projects\Models\Project;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string  $id             UUID
 * @property string   $tenantId
 * @property string   $name
 * @property ?string  $description
 * @property string   $status
 * @property string   $startDate
 * @property ?string  $endDate
 * @property ?Carbon  $createdAt      Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $updatedAt      Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $deletedAt      Internally Carbon, accepts/serializes ISO 8601
 * @property ?UserDTO $owner
 * @property ?array   $users
 * @property ?array   $tasks
 * @property ?array   $requiredSkills
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
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $deletedAt = null,
        public ?UserDTO $owner = null,
        public ?array $users = null,
        #[MapOutputName('tasks')]
        public ?array $tasks = null,
        #[MapOutputName('required_skills')]
        public ?array $requiredSkills = null,
    ) {
    }

    public static function fromModel(Project $model, bool $withRelations = false): self
    {
        return new self(
            tenantId: $model->tenant_id,
            name: $model->name,
            status: $model->status,
            startDate: $model->start_date->format('Y-m-d'),
            id: $model->id,
            description: $model->description,
            endDate: $model->end_date?->format('Y-m-d'),
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            owner: $withRelations && $model->relationLoaded('owner') ? UserDTO::fromModel($model->owner) : null,
            users: $withRelations && $model->relationLoaded('users') ? $model->users->map(fn ($user) => UserDTO::fromModel($user))->toArray() : null,
            tasks: $withRelations && $model->relationLoaded('tasks') ? $model->tasks->map(fn ($task) => TaskDTO::fromModel($task))->toArray() : null,
            requiredSkills: $withRelations && $model->relationLoaded('requiredSkills') ? $model->requiredSkills->map(fn ($skill) => ProjectRequiredSkillDTO::fromModel($skill))->toArray() : null,
        );
    }
}
