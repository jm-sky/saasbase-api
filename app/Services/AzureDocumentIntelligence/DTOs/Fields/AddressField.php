<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

final class AddressField extends ValueWrapper
{
    public function __construct(
        float $confidence,
        ?string $houseNumber,
        ?string $road,
        ?string $postalCode,
        ?string $city,
        ?string $countryRegion,
        ?string $streetAddress,
        ?string $rawAddress
    ) {
        parent::__construct('address', $confidence, [
            'houseNumber'   => $houseNumber,
            'road'          => $road,
            'postalCode'    => $postalCode,
            'city'          => $city,
            'countryRegion' => $countryRegion,
            'streetAddress' => $streetAddress,
            'rawAddress'    => $rawAddress,
        ]);
    }

    public static function fromArray(array $data): static
    {
        return new self(
            confidence: $data['confidence'] ?? 0,
            houseNumber: $data['houseNumber'] ?? null,
            road: $data['road'] ?? null,
            postalCode: $data['postalCode'] ?? null,
            city: $data['city'] ?? null,
            countryRegion: $data['countryRegion'] ?? null,
            streetAddress: $data['streetAddress'] ?? null,
            rawAddress: $data['rawAddress'] ?? null,
        );
    }

    public static function fromAzureArray(array $data): static
    {
        return new self(
            confidence: (float) ($data['confidence'] ?? 0),
            houseNumber: $data['valueAddress']['houseNumber'] ?? null,
            road: $data['valueAddress']['road'] ?? null,
            postalCode: $data['valueAddress']['postalCode'] ?? null,
            city: $data['valueAddress']['city'] ?? null,
            countryRegion: $data['valueAddress']['countryRegion'] ?? null,
            streetAddress: $data['valueAddress']['streetAddress'] ?? null,
            rawAddress: $data['content'] ?? null
        );
    }

    public function validate(): void
    {
        if (!is_array($this->value)) {
            throw new \InvalidArgumentException('AddressField value must be an array');
        }

        $requiredFields = ['houseNumber', 'road', 'postalCode', 'city', 'countryRegion', 'streetAddress'];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $this->value)) {
                throw new \InvalidArgumentException("AddressField missing required field: {$field}");
            }
        }
    }

    public function getHouseNumber(): ?string
    {
        return $this->value['houseNumber'];
    }

    public function getRoad(): ?string
    {
        return $this->value['road'];
    }

    public function getPostalCode(): ?string
    {
        return $this->value['postalCode'];
    }

    public function getCity(): ?string
    {
        return $this->value['city'];
    }

    public function getCountryRegion(): ?string
    {
        return $this->value['countryRegion'];
    }

    public function getStreetAddress(): ?string
    {
        return $this->value['streetAddress'];
    }

    public function getFullAddress(): ?string
    {
        return str_replace("\n", ', ', $this->value['rawAddress'] ?? '');
    }
}
