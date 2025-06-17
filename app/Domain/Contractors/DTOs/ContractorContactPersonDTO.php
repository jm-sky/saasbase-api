<?php

namespace App\Domain\Contractors\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Contractors\Models\ContractorContactPerson;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<ContractorContactPerson>
 *
 * @property ?string $id
 * @property ?string $tenantId
 * @property string  $name
 * @property ?string $email
 * @property ?string $phone
 * @property ?string $position
 * @property ?string $description
 * @property string  $contractorId
 * @property ?Carbon $createdAt    Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt    Internally Carbon, accepts/serializes ISO 8601
 */
final class ContractorContactPersonDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $contractorId,
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $position = null,
        public readonly ?string $description = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            name: $data['name'],
            contractorId: $data['contractor_id'],
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            position: $data['position'] ?? null,
            description: $data['description'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }

    public static function fromModel(Model $model): static
    {
        if (!$model instanceof ContractorContactPerson) {
            throw new \InvalidArgumentException('Model must be instance of ContractorContactPerson');
        }

        return new self(
            name: $model->name,
            contractorId: $model->contractor_id,
            id: $model->id,
            tenantId: $model->tenant_id,
            email: $model->email,
            phone: $model->phone,
            position: $model->position,
            description: $model->description,
            createdAt: $model->created_at ? Carbon::parse($model->created_at) : null,
            updatedAt: $model->updated_at ? Carbon::parse($model->updated_at) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'tenantId'     => $this->tenantId,
            'name'         => $this->name,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'position'     => $this->position,
            'description'  => $this->description,
            'contractorId' => $this->contractorId,
            'createdAt'    => $this->createdAt?->toIso8601String(),
            'updatedAt'    => $this->updatedAt?->toIso8601String(),
        ];
    }
}
