<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Projects\Models\ProjectUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<ProjectUser>
 *
 * @property ?string         $id            UUID
 * @property string          $projectId
 * @property string          $userId
 * @property string          $projectRoleId
 * @property ?Carbon         $createdAt     Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon         $updatedAt     Internally Carbon, accepts/serializes ISO 8601
 * @property ?UserDTO        $user
 * @property ?ProjectRoleDTO $role
 */
final class ProjectUserDTO extends BaseDTO
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $userId,
        public readonly string $projectRoleId,
        public readonly ?string $id = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?UserDTO $user = null,
        public ?ProjectRoleDTO $role = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var ProjectUser $model */
        return new self(
            projectId: $model->project_id,
            userId: $model->user_id,
            projectRoleId: $model->project_role_id,
            id: $model->id,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            user: $model->user ? UserDTO::fromModel($model->user) : null,
            role: $model->role ? ProjectRoleDTO::fromModel($model->role) : null,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            projectId: $data['project_id'],
            userId: $data['user_id'],
            projectRoleId: $data['project_role_id'],
            id: $data['id'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            user: isset($data['user']) ? UserDTO::fromArray($data['user']) : null,
            role: isset($data['role']) ? ProjectRoleDTO::fromArray($data['role']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'projectId'     => $this->projectId,
            'userId'        => $this->userId,
            'projectRoleId' => $this->projectRoleId,
            'createdAt'     => $this->createdAt?->toIso8601String(),
            'updatedAt'     => $this->updatedAt?->toIso8601String(),
            'user'          => $this->user?->toArray(),
            'role'          => $this->role?->toArray(),
        ];
    }
}
