<?php

namespace App\Domain\Contractors\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Contractors\Models\Contractor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Contractor>
 *
 * @property ?string $id          UUID
 * @property string  $tenantId    UUID
 * @property string  $name
 * @property string  $email
 * @property ?string $phone
 * @property ?string $country
 * @property ?string $taxId
 * @property ?string $description
 * @property ?bool   $isActive
 * @property ?bool   $isBuyer
 * @property ?bool   $isSupplier
 * @property ?Carbon $createdAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt   Internally Carbon, accepts/serializes ISO 8601
 */
class ContractorDTO extends BaseDTO
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $id = null,
        public readonly ?string $phone = null,
        public readonly ?string $country = null,
        public readonly ?string $taxId = null,
        public readonly ?string $description = null,
        public readonly ?bool $isActive = null,
        public readonly ?bool $isBuyer = null,
        public readonly ?bool $isSupplier = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            tenantId: $data['tenant_id'],
            name: $data['name'],
            email: $data['email'],
            id: $data['id'],
            phone: $data['phone'],
            country: $data['country'],
            taxId: $data['tax_id'],
            description: $data['description'],
            isActive: $data['is_active'],
            isBuyer: $data['is_buyer'],
            isSupplier: $data['is_supplier'],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
            deletedAt: $data['deleted_at'],
        );
    }

    public static function fromModel(Model $model): static
    {
        if (!$model instanceof Contractor) {
            throw new \InvalidArgumentException('Model must be instance of Contractor');
        }

        return new self(
            tenantId: $model->tenant_id,
            name: $model->name,
            email: $model->email,
            id: $model->id,
            phone: $model->phone,
            country: $model->country,
            taxId: $model->tax_id,
            description: $model->description,
            isActive: $model->is_active,
            isBuyer: $model->is_buyer,
            isSupplier: $model->is_supplier,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'tenantId'    => $this->tenantId,
            'name'        => $this->name,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'country'     => $this->country,
            'taxId'       => $this->taxId,
            'description' => $this->description,
            'isActive'    => $this->isActive,
            'isBuyer'     => $this->isBuyer,
            'isSupplier'  => $this->isSupplier,
            'createdAt'   => $this->createdAt?->toIso8601String(),
            'updatedAt'   => $this->updatedAt?->toIso8601String(),
            'deletedAt'   => $this->deletedAt?->toIso8601String(),
        ];
    }
}
