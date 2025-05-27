<?php

namespace App\Services\GusLookup\DTOs;

use App\Services\GusLookup\Enums\EntityType;
use Illuminate\Contracts\Support\Arrayable;

/**
 * GUS Lookup Result Data Transfer Object.
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
class GusLookupResultDTO implements Arrayable, \JsonSerializable
{
    public function __construct(
        public string $name,
        public EntityType $type,
        public ?string $regon,
        public ?string $nip,
        public ?string $voivodeship,
        public ?string $province,
        public ?string $community,
        public ?string $city,
        public ?string $postalCode,
        public ?string $street,
        public ?string $building,
        public ?string $flat,
        public ?string $silosId,
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        return new self(
            regon: $data['regon'] ?? null,
            nip: $data['nip'] ?? null,
            type: $data['type'] ?? null,
            name: $data['name'] ?? null,
            voivodeship: $data['voivodeship'] ?? null,
            province: $data['province'] ?? null,
            community: $data['community'] ?? null,
            city: $data['city'] ?? null,
            postalCode: $data['postalCode'] ?? null,
            street: $data['street'] ?? null,
            building: $data['building'] ?? null,
            flat: $data['flat'] ?? null,
            silosId: $data['silosId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'regon'       => $this->regon,
            'nip'         => $this->nip,
            'type'        => $this->type,
            'name'        => $this->name,
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
}
