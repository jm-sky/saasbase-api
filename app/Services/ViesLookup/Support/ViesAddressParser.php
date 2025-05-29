<?php

namespace App\Services\ViesLookup\Support;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\Enums\AddressType;
use App\Services\ViesLookup\Support\ViesParser\DTO\ViesAddress;
use App\Services\ViesLookup\Support\ViesParser\ViesParser;

class ViesAddressParser
{
    public static function parse(string $countryCode, string $vatNumber, string $address, AddressType $type = AddressType::REGISTERED_OFFICE): AddressDTO
    {
        $parser = new ViesParser(
            vatNumber: $vatNumber,
            address: $address,
            countryCode: $countryCode,
        );

        /** @var ?ViesAddress $parsedAddress */
        $parsedAddress = $parser->getParsedAddress();

        if ($parsedAddress) {
            return new AddressDTO(
                country: $countryCode,
                city: $parsedAddress->city,
                postalCode: $parsedAddress->zip,
                street: $parsedAddress->street,
                building: $parsedAddress->building,
                flat: $parsedAddress->flat,
                description: null,
                type: $type,
                isDefault: true,
            );
        }

        return new AddressDTO(
            country: $countryCode,
            city: '',
            postalCode: null,
            street: $address,
            building: null,
            flat: null,
            description: null,
            type: $type,
            isDefault: true,
        );
    }
}
