<?php

namespace App\Services\ViesLookup\DTOs;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Common\Enums\AddressType;

class ViesLookupResultDTO
{
    public function __construct(
        public readonly string $countryCode,
        public readonly string $vatNumber,
        public readonly bool $valid,
        public readonly ?string $name,
        public readonly ?string $address,
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        return new self(
            countryCode: $data['countryCode'] ?? '',
            vatNumber: $data['vatNumber'] ?? '',
            valid: (bool) ($data['valid'] ?? false),
            name: $data['name'] ?? null,
            address: $data['address'] ?? null,
        );
    }

    public function toCommonLookupData(): CommonCompanyLookupData
    {
        $address = null;

        if ($this->address) {
            $address = new AddressDTO(
                country: $this->countryCode,
                city: '', // We don't have city in VIES data
                type: AddressType::REGISTERED_OFFICE,
                isDefault: true,
                street: $this->address
            );
        }

        return new CommonCompanyLookupData(
            name: $this->name ?? '',
            country: $this->countryCode,
            vatId: $this->vatNumber,
            address: $address,
            bankAccount: null // VIES doesn't provide bank account information
        );
    }
}
