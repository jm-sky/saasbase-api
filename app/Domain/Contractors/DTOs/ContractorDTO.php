<?php

namespace App\Domain\Contractors\DTOs;

use App\Domain\Contractors\Models\Contractor;
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
 * @property bool $isActive
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
        public ?bool $isActive = true,
    ) {
    }

    public static function fromModel(Contractor $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            phone: $model->phone,
            address: $model->address,
            city: $model->city,
            state: $model->state,
            zipCode: $model->zip_code,
            country: $model->country,
            taxId: $model->tax_id,
            notes: $model->notes,
            isActive: $model->is_active,
        );
    }
}
