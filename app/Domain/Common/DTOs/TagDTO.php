<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Enums\TagColor;
use App\Domain\Common\Models\Tag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Tag>
 *
 * @property ?string  $id
 * @property string   $tenantId
 * @property string   $name
 * @property string   $slug
 * @property TagColor $color
 * @property ?Carbon  $createdAt
 * @property ?Carbon  $updatedAt
 */
final class TagDTO extends BaseDTO
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $name,
        public readonly string $slug,
        public readonly TagColor $color,
        public readonly ?string $id = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var Tag $model */
        return new self(
            name: $model->name,
            slug: $model->slug,
            id: $model->id,
            color: $model->color,
            tenantId: $model->tenant_id,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            slug: $data['slug'],
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'],
            color: TagColor::from($data['color']),
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
            'slug'      => $this->slug,
            'color'     => $this->color,
            'createdAt' => $this->createdAt?->toIso8601String(),
            'updatedAt' => $this->updatedAt?->toIso8601String(),
        ];
    }
}
