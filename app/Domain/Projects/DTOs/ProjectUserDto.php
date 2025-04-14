<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDto;
use Carbon\Carbon;

class ProjectUserDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $project_id,
        public readonly string $user_id,
        public readonly string $project_role_id,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?Carbon $deleted_at,
        public readonly ?UserDto $user = null,
        public readonly ?ProjectRoleDto $role = null,
    ) {}

    public static function fromModel(\App\Domain\Projects\Models\ProjectUser $model, bool $withRelations = false): self
    {
        return new self(
            id: $model->id,
            project_id: $model->project_id,
            user_id: $model->user_id,
            project_role_id: $model->project_role_id,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
            deleted_at: $model->deleted_at,
            user: $withRelations && $model->relationLoaded('user')
                ? UserDto::fromModel($model->user)
                : null,
            role: $withRelations && $model->relationLoaded('role')
                ? ProjectRoleDto::fromModel($model->role)
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'user_id' => $this->user_id,
            'project_role_id' => $this->project_role_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'user' => $this->user?->toArray(),
            'role' => $this->role?->toArray(),
        ];
    }
}
