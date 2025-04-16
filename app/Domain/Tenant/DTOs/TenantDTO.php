<?php

namespace App\Domain\Tenant\DTOs;

use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $name
 * @property string $slug
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class TenantDTO extends Data
{
    public function __construct(
        public ?string $id = null,
        public string $name,
        public string $slug,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}
}
