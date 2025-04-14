<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDto;
use Carbon\Carbon;

class ProjectDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenant_id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $status,
        public readonly Carbon $start_date,
        public readonly ?Carbon $end_date,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?Carbon $deleted_at,
        public readonly ?UserDto $owner = null,
        public readonly ?array $users = null,
        public readonly ?array $tasks = null,
        public readonly ?array $requiredSkills = null,
    ) {}

    public static function fromModel(\App\Domain\Projects\Models\Project $model, bool $withRelations = false): self
    {
        return new self(
            id: $model->id,
            tenant_id: $model->tenant_id,
            name: $model->name,
            description: $model->description,
            status: $model->status,
            start_date: $model->start_date,
            end_date: $model->end_date,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
            deleted_at: $model->deleted_at,
            owner: $withRelations && $model->relationLoaded('owner')
                ? UserDto::fromModel($model->owner)
                : null,
            users: $withRelations && $model->relationLoaded('users')
                ? $model->users->map(fn($user) => ProjectUserDto::fromModel($user, true))->toArray()
                : null,
            tasks: $withRelations && $model->relationLoaded('tasks')
                ? $model->tasks->map(fn($task) => TaskDto::fromModel($task, true))->toArray()
                : null,
            requiredSkills: $withRelations && $model->relationLoaded('requiredSkills')
                ? $model->requiredSkills->map(fn($skill) => ProjectRequiredSkillDto::fromModel($skill, true))->toArray()
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'owner' => $this->owner?->toArray(),
            'users' => $this->users?->map(fn($user) => $user->toArray())->toArray(),
            'tasks' => $this->tasks?->map(fn($task) => $task->toArray())->toArray(),
            'required_skills' => $this->requiredSkills?->map(fn($skill) => $skill->toArray())->toArray(),
        ];
    }
}
