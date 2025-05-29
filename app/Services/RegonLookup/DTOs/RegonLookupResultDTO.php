<?php

namespace App\Services\RegonLookup\DTOs;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Common\Enums\AddressType;
use App\Services\RegonLookup\Enums\EntityType;
use Illuminate\Contracts\Support\Arrayable;

/**
 * REGON Lookup Result Data Transfer Object.
 *
 * @property string     $name        Key: Nazwa           | Example: "Example Company Sp. z o.o."
 * @property ?string    $regon       Key: Regon           | Example: "123456789"
 * @property ?string    $nip         Key: Nip             | Example: "1234567890"
 * @property EntityType $type        Key: Typ             | Example: "P"
 * @property ?string    $statusNip   Key: StatusNip       | Example: "1"
 * @property ?string    $dateOfEnd   Key: DataZakonczeniaDzialalnosci | Example: "2025-01-01"
 * @property ?string    $voivodeship Key: Wojewodztwo     | Example: "Mazowieckie"
 * @property ?string    $province    Key: Powiat          | Example: "Warszawa"
 * @property ?string    $community   Key: Gmina           | Example: "Warszawa"
 * @property ?string    $city        Key: Miejscowosc     | Example: "Warszawa"
 * @property ?string    $postalCode  Key: KodPocztowy     | Example: "00-001"
 * @property ?string    $street      Key: Ulica           | Example: "ul. Kwiatowa"
 * @property ?string    $building    Key: NrNieruchomosci | Example: "15"
 * @property ?string    $flat        Key: NrLokalu        | Example: "1"
 * @property ?string    $silosId     Key: SilosID         | Example: "6"
 */
class RegonLookupResultDTO implements Arrayable, \JsonSerializable
{
    public function __construct(
        public readonly string $name,
        public readonly string $regon,
        public readonly string $nip,
        public readonly EntityType $type,
        public readonly ?string $statusNip,
        public readonly ?string $dateOfEnd,
        public readonly ?string $voivodeship,
        public readonly ?string $province,
        public readonly ?string $community,
        public readonly ?string $city,
        public readonly ?string $postalCode,
        public readonly ?string $street,
        public readonly ?string $building,
        public readonly ?string $flat,
        public readonly ?string $silosId,
    ) {
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'regon'       => $this->regon,
            'nip'         => $this->nip,
            'type'        => $this->type,
            'statusNip'   => $this->statusNip,
            'dateOfEnd'   => $this->dateOfEnd,
            'voivodeship' => $this->voivodeship,
            'province'    => $this->province,
            'community'   => $this->community,
            'city'        => $this->city,
            'postalCode'  => $this->postalCode,
            'street'      => $this->street,
            'building'    => $this->building,
            'flat'        => $this->flat,
            'silosId'     => $this->silosId,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toCommonLookupData(): CommonCompanyLookupData
    {
        $address = new AddressDTO(
            country: 'PL',
            city: $this->city,
            type: AddressType::REGISTERED_OFFICE,
            postalCode: $this->postalCode,
            street: $this->street,
            building: $this->building,
            flat: $this->flat,
            isDefault: true,
        );

        return new CommonCompanyLookupData(
            name: $this->name,
            country: 'PL',
            vatId: $this->nip,
            regon: $this->regon,
            shortName: null,
            phoneNumber: null,
            email: null,
            website: null,
            address: $address,
            bankAccount: null,
        );
    }
}
