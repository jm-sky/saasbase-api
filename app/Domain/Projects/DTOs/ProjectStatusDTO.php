<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Projects\Models\ProjectStatus;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

/**
 * @property ?string $id        UUID
 * @property string  $tenantId  UUID
 * @property string  $name
 * @property string  $color
 * @property int     $sortOrder
 * @property bool    $isDefault
 * @property ?string $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?string $updatedAt Internally Carbon, accepts/serializes ISO 8601
 */
class ProjectStatusDTO extends Data
{
    public function __construct(
        public ?string $id,
        public string $name,
        public string $color,
        public int $sortOrder,
        public bool $isDefault,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public static function fromModel(ProjectStatus $status): self
    {
        return new self(
            id: $status->id,
            name: $status->name,
            color: $status->color,
            sortOrder: $status->sort_order,
            isDefault: $status->is_default,
            createdAt: $status->created_at?->toISOString(),
            updatedAt: $status->updated_at?->toISOString(),
        );
    }

    /**
     * @param ProjectStatus[] $statuses
     */
    public static function collect($statuses): DataCollection
    {
        return new DataCollection(ProjectStatus::class, collect($statuses)->map(fn ($status) => self::from($status)));
    }
}
