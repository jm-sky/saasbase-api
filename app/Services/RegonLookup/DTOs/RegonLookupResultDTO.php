<?php

namespace App\Services\RegonLookup\DTOs;

use App\Services\Common\DTOs\AddressDTO;
use App\Services\Common\DTOs\CommonCompanyLookupData;
use App\Services\RegonLookup\Enums\EntityType;
use Illuminate\Contracts\Support\Arrayable;

/**
 * REGON Lookup Result Data Transfer Object.
 *
 * @property string     $name        Example: "Example Company Sp. z o.o."
 * @property ?string    $regon       Example: "123456789"
 * @property ?string    $nip         Example: "1234567890"
 * @property EntityType $type        Example: "P"
 * @property ?string    $voivodeship Example: "Mazowieckie"
 * @property ?string    $province    Example: "Warszawa"
 * @property ?string    $community   Example: "Warszawa"
 * @property ?string    $city        Example: "Warszawa"
 * @property ?string    $postalCode  Example: "00-001"
 * @property ?string    $street      Example: "ul. Kwiatowa"
 * @property ?string    $building    Example: "15"
 * @property ?string    $flat        Example: "1"
 * @property ?string    $silosId     Example: "6"
 */
class RegonLookupResultDTO implements Arrayable, \JsonSerializable
{
    public function __construct(
        public readonly string $regon,
        public readonly string $nip,
        public readonly string $name,
        public readonly ?string $shortName,
        public readonly ?string $registrationNumber,
        public readonly ?string $registrationDate,
        public readonly ?string $startDate,
        public readonly ?string $endDate,
        public readonly ?string $phoneNumber,
        public readonly ?string $email,
        public readonly ?string $website,
        public readonly ?AddressDTO $address
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        return new self(
            regon: $data['regon'],
            nip: $data['nip'],
            name: $data['name'],
            shortName: $data['shortName'] ?? null,
            registrationNumber: $data['registrationNumber'] ?? null,
            registrationDate: $data['registrationDate'] ?? null,
            startDate: $data['startDate'] ?? null,
            endDate: $data['endDate'] ?? null,
            phoneNumber: $data['phoneNumber'] ?? null,
            email: $data['email'] ?? null,
            website: $data['website'] ?? null,
            address: isset($data['address']) ? AddressDTO::fromArray($data['address']) : null
        );
    }

    public function toArray(): array
    {
        return [
            'regon'              => $this->regon,
            'nip'                => $this->nip,
            'name'               => $this->name,
            'shortName'          => $this->shortName,
            'registrationNumber' => $this->registrationNumber,
            'registrationDate'   => $this->registrationDate,
            'startDate'          => $this->startDate,
            'endDate'            => $this->endDate,
            'phoneNumber'        => $this->phoneNumber,
            'email'              => $this->email,
            'website'            => $this->website,
            'address'            => $this->address?->toArray(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toCommonLookupData(): CommonCompanyLookupData
    {
        return new CommonCompanyLookupData(
            name: $this->name,
            nip: $this->nip,
            regon: $this->regon,
            shortName: $this->shortName,
            phoneNumber: $this->phoneNumber,
            email: $this->email,
            website: $this->website,
            address: $this->address,
            bankAccount: null // REGON does not provide bank account information
        );
    }
}
