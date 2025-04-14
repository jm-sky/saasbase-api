<?php

namespace App\Domain\Contractors\DTO;

use Spatie\LaravelData\Data;

/**
 * @property string|null $id UUID
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip_code
 * @property string|null $country
 * @property string|null $tax_id
 * @property string|null $notes
 * @property bool $is_active
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
        public ?string $zip_code = null,
        public ?string $country = null,
        public ?string $tax_id = null,
        public ?string $notes = null,
        public bool $is_active = true,
    ) {}
}
