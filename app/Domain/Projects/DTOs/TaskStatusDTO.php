<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Projects\Models\TaskStatus;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

/**
 * @property ?string $id        UUID
 * @property string  $tenantId  UUID
 * @property string  $name
 * @property string  $color
 * @property int     $sortOrder
 * @property bool    $isDefault
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 */
class TaskStatusDTO extends Data
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

    public static function fromModel(TaskStatus $status): self
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
     * @param TaskStatus[] $statuses
     */
    public static function collect($statuses): DataCollection
    {
        return new DataCollection(TaskStatus::class, collect($statuses)->map(fn ($status) => self::from($status)));
    }
}
