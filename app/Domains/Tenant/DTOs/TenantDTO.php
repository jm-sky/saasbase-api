<?php

namespace App\Domains\Tenant\DTOs;

use Spatie\LaravelData\Data;

/**
 * @property string|null $id UUID
 * @property string $name
 * @property string $slug
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class TenantDTO extends Data
{
    public function __construct(
        public ?string $id = null,
        public string $name,
        public string $slug,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $deleted_at = null,
    ) {}
}
