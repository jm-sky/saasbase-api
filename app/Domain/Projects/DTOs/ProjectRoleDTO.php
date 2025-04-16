<?php

namespace App\Domain\Projects\DTOs;

use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $tenantId
 * @property string $name
 * @property ?string $description
 * @property ?array $permissions
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class ProjectRoleDTO extends Data
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $name,
        public readonly ?string $id = null,
        public readonly ?string $description = null,
        public readonly ?array $permissions = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}
}
