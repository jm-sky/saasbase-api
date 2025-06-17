<?php

namespace App\Services\ViesLookup\DTOs;

use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Services\ViesLookup\Support\ViesAddressParser;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * VIES Check Result Data Transfer Object.
 *
 * @property bool    $valid       Whether the VAT number is valid
 * @property string  $countryCode Country code (e.g. "PL")
 * @property string  $vatNumber   VAT number
 * @property string  $requestDate Date of the request
 * @property ?string $name        Company name (if available)
 * @property ?string $address     Company address (if available)
 * @property ?string $rawAddress  Raw address from VIES
 * @property ?bool   $cache       Whether the data was cached
 */
final class ViesLookupResultDTO implements Arrayable, \JsonSerializable
{
    public function __construct(
        public readonly bool $valid,
        public readonly string $countryCode,
        public readonly string $vatNumber,
        public readonly string $requestDate,
        public readonly ?string $name,
        public readonly ?string $address,
        public readonly ?string $rawAddress,
        public readonly ?bool $cache = null,
    ) {
    }

    public static function fromXml(\SimpleXMLElement $xml): self
    {
        return new self(
            valid: ('true' === (string) $xml->xpath('//urn:valid')[0]),
            countryCode: (string) Arr::get($xml->xpath('//urn:countryCode'), 0),
            vatNumber: (string) Arr::get($xml->xpath('//urn:vatNumber'), 0),
            requestDate: (string) Arr::get($xml->xpath('//urn:requestDate'), 0),
            name: (string) Arr::get($xml->xpath('//urn:name'), 0),
            address: trim(preg_replace('!\s+!', ' ', (string) $xml->xpath('//urn:address')[0])),
            rawAddress: (string) $xml->xpath('//urn:address')[0],
            cache: null,
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
            'cache'       => $this->cache,
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
            $address = ViesAddressParser::parse($this->countryCode, $this->vatNumber, $this->rawAddress);
        }

        return new CommonCompanyLookupData(
            name: $this->name ?? '',
            country: $this->countryCode,
            vatId: $this->vatNumber,
            address: $address,
            bankAccount: null, // VIES doesn't provide bank account information
            cache: $this->cache,
        );
    }
}
