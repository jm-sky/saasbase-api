<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Projects\Models\ProjectUser;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string         $id            UUID
 * @property string          $projectId
 * @property string          $userId
 * @property string          $projectRoleId
 * @property ?Carbon         $createdAt     Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon         $updatedAt     Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon         $deletedAt     Internally Carbon, accepts/serializes ISO 8601
 * @property ?UserDTO        $user
 * @property ?ProjectRoleDTO $role
 */
class ProjectUserDTO extends Data
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $userId,
        public readonly string $projectRoleId,
        public readonly ?string $id = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $deletedAt = null,
        public ?UserDTO $user = null,
        public ?ProjectRoleDTO $role = null,
    ) {
    }

    public static function fromModel(ProjectUser $model): self
    {
        return new self(
            projectId: $model->project_id,
            userId: $model->user_id,
            projectRoleId: $model->project_role_id,
            id: $model->id,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            user: $model->user ? UserDTO::fromModel($model->user) : null,
            role: $model->role ? ProjectRoleDTO::fromModel($model->role) : null,
        );
    }
}
