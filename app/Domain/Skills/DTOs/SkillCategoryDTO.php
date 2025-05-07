<?php

namespace App\Domain\Skills\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Skills\Models\SkillCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<SkillCategory>
 *
 * @property ?string $id          UUID
 * @property string  $name
 * @property ?string $description
 * @property ?Carbon $createdAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt   Internally Carbon, accepts/serializes ISO 8601
 */
class SkillCategoryDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $id = null,
        public ?string $description = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var SkillCategory $model */
        if (!$model->name) {
            throw new \InvalidArgumentException('SkillCategory name is required');
        }

        return new static(
            name: $model->name,
            id: $model->id,
            description: $model->description,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }

    public static function fromArray(array $data): static
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('SkillCategory name is required');
        }

        return new static(
            name: $data['name'],
            id: $data['id'] ?? null,
            description: $data['description'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? Carbon::parse($data['deleted_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'createdAt'   => $this->createdAt?->toIso8601String(),
            'updatedAt'   => $this->updatedAt?->toIso8601String(),
            'deletedAt'   => $this->deletedAt?->toIso8601String(),
        ];
    }
}
