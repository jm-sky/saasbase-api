<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Projects\Models\TaskStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<TaskStatus>
 *
 * @property ?string $id        UUID
 * @property string  $tenantId  UUID
 * @property string  $name
 * @property string  $color
 * @property int     $sortOrder
 * @property bool    $isDefault
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 */
class TaskStatusDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $color,
        public readonly int $sortOrder,
        public readonly bool $isDefault,
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var TaskStatus $model */
        return new static(
            name: $model->name,
            color: $model->color,
            sortOrder: $model->sort_order,
            isDefault: $model->is_default,
            id: $model->id,
            tenantId: $model->tenant_id,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            color: $data['color'],
            sortOrder: $data['sort_order'],
            isDefault: $data['is_default'],
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'tenantId'  => $this->tenantId,
            'name'      => $this->name,
            'color'     => $this->color,
            'sortOrder' => $this->sortOrder,
            'isDefault' => $this->isDefault,
            'createdAt' => $this->createdAt?->toIso8601String(),
            'updatedAt' => $this->updatedAt?->toIso8601String(),
        ];
    }
}
