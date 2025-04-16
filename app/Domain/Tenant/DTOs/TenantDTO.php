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
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}
}
