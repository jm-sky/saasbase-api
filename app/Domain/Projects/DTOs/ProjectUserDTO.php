<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Projects\Models\ProjectUser;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $projectId
 * @property string $userId
 * @property string $projectRoleId
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 * @property ?UserDTO $user
 * @property ?ProjectRoleDTO $role
 */
class ProjectUserDTO extends Data
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $userId,
        public readonly string $projectRoleId,
        public readonly ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
        public ?UserDTO $user = null,
        public ?ProjectRoleDTO $role = null,
    ) {}

    public static function fromModel(ProjectUser $model): self
    {
        return new self(
            projectId: $model->project_id,
            userId: $model->user_id,
            projectRoleId: $model->project_role_id,
            id: $model->id,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
            user: $model->user ? UserDTO::fromModel($model->user) : null,
            role: $model->role ? ProjectRoleDTO::fromModel($model->role) : null,
        );
    }
}
