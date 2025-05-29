<?php

namespace App\Services\MfLookup\DTOs;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Ministry of Finance Address Data Transfer Object.
 *
 * @property string $street          Example: "POZNAŃSKA"
 * @property string $buildingAndFlat Example: "16/4"
 * @property string $postalCode      Example: "00-680"
 * @property string $city            Example: "WARSZAWA"
 *
 * Input: "POZNAŃSKA 16/4, 00-680 WARSZAWA"
 * Output: MfAddressDTO(street="POZNAŃSKA", buildingAndFlat="16/4", postalCode="00-680", city="WARSZAWA")
 */
class MfAddressDTO implements Arrayable, \JsonSerializable
{
    public function __construct(
        public readonly string $street,
        public readonly string $buildingAndFlat,
        public readonly string $postalCode,
        public readonly string $city,
    ) {
    }

    public static function fromString(?string $address): ?self
    {
        if (empty($address)) {
            return null;
        }

        // Split address into street part and city part
        $parts = explode(',', $address, 2);

        if (2 !== count($parts)) {
            return null;
        }

        $streetPart = trim($parts[0]);
        $cityPart   = trim($parts[1]);

        // Extract postal code and city
        if (!preg_match('/^(\d{2}-\d{3})\s+(.+)$/', $cityPart, $cityMatches)) {
            return null;
        }

        $postalCode = $cityMatches[1];
        $city       = $cityMatches[2];

        // Extract street name and building number
        if (!preg_match('/^(.+?)\s+(\d+(?:\/\d+)?)$/', $streetPart, $streetMatches)) {
            return null;
        }

        $street          = trim($streetMatches[1]);
        $buildingAndFlat = $streetMatches[2];

        return new self(
            street: $street,
            buildingAndFlat: $buildingAndFlat,
            postalCode: $postalCode,
            city: $city
        );
    }

    public function toArray(): array
    {
        return [
            'street'          => $this->street,
            'buildingAndFlat' => $this->buildingAndFlat,
            'postalCode'      => $this->postalCode,
            'city'            => $this->city,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toString(): string
    {
        return sprintf(
            '%s %s, %s %s',
            $this->street,
            $this->buildingAndFlat,
            $this->postalCode,
            $this->city
        );
    }
}
