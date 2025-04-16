<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
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
}
