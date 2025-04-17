<?php

namespace App\Domain\Contractors\DTOs;

use App\Domain\Contractors\Models\Contractor;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $name
 * @property string $email
 * @property ?string $phone
 * @property ?string $address
 * @property ?string $city
 * @property ?string $state
 * @property ?string $zipCode
 * @property ?string $country
 * @property ?string $taxId
 * @property ?string $notes
 * @property ?bool $isActive
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt Internally Carbon, accepts/serializes ISO 8601
 */
class ContractorDTO extends Data
{
    public function __construct(
        public ?string $id = null,
        public string $name,
        public string $email,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $zipCode = null,
        public ?string $country = null,
        public ?string $taxId = null,
        public ?string $notes = null,
        public ?bool $isActive = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $deletedAt = null,
    ) {
    }

    public static function fromModel(Contractor $contractor): self
    {
        return new self(
            id: $contractor->id,
            name: $contractor->name,
            email: $contractor->email,
            phone: $contractor->phone,
            address: $contractor->address,
            city: $contractor->city,
            state: $contractor->state,
            zipCode: $contractor->zip_code,
            country: $contractor->country,
            taxId: $contractor->tax_id,
            notes: $contractor->notes,
            isActive: $contractor->is_active,
            createdAt: $contractor->created_at,
            updatedAt: $contractor->updated_at,
            deletedAt: $contractor->deleted_at,
        );
    }
}
