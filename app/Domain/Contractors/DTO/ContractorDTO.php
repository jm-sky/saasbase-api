<?php

namespace App\Domain\Contractors\DTO;

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
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $id = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $zipCode = null,
        public ?string $country = null,
        public ?string $taxId = null,
        public ?string $notes = null,
        public bool $isActive = true,
    ) {}
}
