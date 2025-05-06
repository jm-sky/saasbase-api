<?php

namespace App\Domain\Contractors\DTOs;

use App\Domain\Contractors\Models\Contractor;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

/**
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
class ContractorDTO implements Arrayable
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

    public static function fromModel(Contractor $contractor): self
    {
        return new self(
            tenantId: $contractor->tenant_id,
            name: $contractor->name,
            email: $contractor->email,
            id: $contractor->id,
            phone: $contractor->phone,
            country: $contractor->country,
            taxId: $contractor->tax_id,
            description: $contractor->description,
            isActive: $contractor->is_active,
            isBuyer: $contractor->is_buyer,
            isSupplier: $contractor->is_supplier,
            createdAt: $contractor->created_at,
            updatedAt: $contractor->updated_at,
            deletedAt: $contractor->deleted_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenantId' => $this->tenantId,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => $this->country,
            'taxId' => $this->taxId,
            'description' => $this->description,
            'isActive' => $this->isActive,
            'isBuyer' => $this->isBuyer,
            'isSupplier' => $this->isSupplier,
            'createdAt' => $this->createdAt->toIso8601String(),
            'updatedAt' => $this->updatedAt->toIso8601String(),
            'deletedAt' => $this->deletedAt->toIso8601String(),
        ];
    }
}
