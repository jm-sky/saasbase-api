<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
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
}
