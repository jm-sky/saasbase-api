<?php

namespace App\Domain\Skills\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Skills\Models\Skill;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Skill>
 *
 * @property ?string $id          UUID
 * @property string  $name
 * @property ?string $category
 * @property ?string $description
 * @property ?Carbon $createdAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt   Internally Carbon, accepts/serializes ISO 8601
 */
final class SkillDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $category,
        public readonly ?string $id = null,
        public readonly ?string $description = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var Skill $model */
        return new self(
            name: $model->name,
            category: $model->category,
            id: $model->id,
            description: $model->description,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            category: $data['category'],
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
            'category'    => $this->category,
            'description' => $this->description,
            'createdAt'   => $this->createdAt?->toIso8601String(),
            'updatedAt'   => $this->updatedAt?->toIso8601String(),
            'deletedAt'   => $this->deletedAt?->toIso8601String(),
        ];
    }
}
