<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Projects\Models\Project;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string  $id             UUID
 * @property string   $tenantId       UUID
 * @property string   $name
 * @property ?string  $description
 * @property string   $statusId       UUID
 * @property string   $ownerId        UUID
 * @property ?Carbon  $startDate      Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $endDate        Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $createdAt      Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $updatedAt      Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon  $deletedAt      Internally Carbon, accepts/serializes ISO 8601
 * @property ?UserDTO $owner
 * @property ?array   $users
 * @property ?array   $tasks
 * @property ?array   $requiredSkills
 */
class ProjectDTO extends Data
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $name,
        public readonly string $statusId,
        public readonly string $ownerId,
        public readonly ?string $description = null,
        public readonly ?string $id = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        #[MapOutputName('start_date')]
        public ?Carbon $startDate = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        #[MapOutputName('end_date')]
        public ?Carbon $endDate = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $deletedAt = null,
        public ?UserDTO $owner = null,
        public ?array $users = null,
        #[MapOutputName('tasks')]
        public ?array $tasks = null,
        #[MapOutputName('required_skills')]
        public ?array $requiredSkills = null,
    ) {
    }

    public static function fromModel(Project $model, bool $withRelations = false): self
    {
        return new self(
            tenantId: $model->tenant_id,
            name: $model->name,
            statusId: $model->status_id,
            ownerId: $model->owner_id,
            description: $model->description,
            id: $model->id,
            startDate: $model->start_date,
            endDate: $model->end_date,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            owner: $withRelations && $model->relationLoaded('owner') ? UserDTO::fromModel($model->owner) : null,
            users: $withRelations && $model->relationLoaded('users') ? UserDTO::collect($model->users)->toArray() : null,
            tasks: $withRelations && $model->relationLoaded('tasks') ? TaskDTO::collect($model->tasks)->toArray() : null,
            requiredSkills: $withRelations && $model->relationLoaded('requiredSkills') ? $model->requiredSkills->map(fn ($skill) => ProjectRequiredSkillDTO::fromModel($skill))->toArray() : null,
        );
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        // Ensure dates are properly formatted
        $data['startDate'] = $this->startDate?->format('Y-m-d');
        $data['endDate']   = $this->endDate?->format('Y-m-d');
        $data['createdAt'] = $this->createdAt?->toIso8601String();
        $data['updatedAt'] = $this->updatedAt?->toIso8601String();
        $data['deletedAt'] = $this->deletedAt?->toIso8601String();

        return $data;
    }
}
