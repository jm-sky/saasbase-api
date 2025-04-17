<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Projects\Models\ProjectRole;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $tenantId
 * @property string $name
 * @property ?string $description
 * @property ?array $permissions
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt Internally Carbon, accepts/serializes ISO 8601
 */
class ProjectRoleDTO extends Data
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $name,
        public readonly ?string $id = null,
        public readonly ?string $description = null,
        public readonly ?array $permissions = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $deletedAt = null,
    ) {}

    public static function fromModel(ProjectRole $model): self
    {
        return new self(
            tenantId: $model->tenant_id,
            name: $model->name,
            id: $model->id,
            description: $model->description,
            permissions: $model->permissions,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }
}
