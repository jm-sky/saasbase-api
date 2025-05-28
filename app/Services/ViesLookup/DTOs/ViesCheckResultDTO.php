<?php

namespace App\Services\ViesLookup\DTOs;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Common\Enums\AddressType;
use Illuminate\Contracts\Support\Arrayable;

/**
 * VIES Check Result Data Transfer Object.
 *
 * @property bool    $valid       Whether the VAT number is valid
 * @property string  $countryCode Country code (e.g. "PL")
 * @property string  $vatNumber   VAT number
 * @property string  $requestDate Date of the request
 * @property ?string $name        Company name (if available)
 * @property ?string $address     Company address (if available)
 */
class ViesCheckResultDTO implements Arrayable, \JsonSerializable
{
    public function __construct(
        public readonly bool $valid,
        public readonly string $countryCode,
        public readonly string $vatNumber,
        public readonly string $requestDate,
        public readonly ?string $name,
        public readonly ?string $address,
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        return new self(
            valid: (bool) ($data['valid'] ?? false),
            countryCode: $data['countryCode'] ?? '',
            vatNumber: $data['vatNumber'] ?? '',
            requestDate: $data['requestDate'] ?? '',
            name: $data['name'] ?? null,
            address: $data['address'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'valid'       => $this->valid,
            'countryCode' => $this->countryCode,
            'vatNumber'   => $this->vatNumber,
            'requestDate' => $this->requestDate,
            'name'        => $this->name,
            'address'     => $this->address,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
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
