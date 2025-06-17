<?php

namespace App\Domain\Common\DTOs;

/**
 * @property string                      $name
 * @property string                      $country
 * @property ?string                     $vatId
 * @property ?string                     $regon
 * @property ?string                     $shortName
 * @property ?string                     $phoneNumber
 * @property ?string                     $email
 * @property ?string                     $website
 * @property ?AddressDTO                 $address
 * @property ?BankAccountDTO             $bankAccount
 * @property ?CommonCompanyLookupSources $sources
 * @property ?bool                       $cache
 */
class CommonCompanyLookupData extends BaseDataDTO
{
    public function __construct(
        public string $name,
        public string $country,
        public ?string $vatId = null,
        public ?string $regon = null,
        public ?string $shortName = null,
        public ?string $phoneNumber = null,
        public ?string $email = null,
        public ?string $website = null,
        public ?AddressDTO $address = null,
        public ?BankAccountDTO $bankAccount = null,
        public ?CommonCompanyLookupSources $sources = null,
        public ?bool $cache = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            name: $data['name'],
            country: $data['country'],
            vatId: $data['vatId'],
            regon: $data['regon'],
            shortName: $data['shortName'],
            phoneNumber: $data['phoneNumber'],
            email: $data['email'],
            website: $data['website'],
            address: $data['address'] ? AddressDTO::fromArray($data['address']) : null,
            bankAccount: $data['bankAccount'] ? BankAccountDTO::fromArray($data['bankAccount']) : null,
            sources: $data['sources'] ? CommonCompanyLookupSources::fromArray($data['sources']) : null,
            cache: $data['cache'],
        );
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'country'     => $this->country,
            'vatId'       => $this->vatId,
            'regon'       => $this->regon,
            'shortName'   => $this->shortName,
            'phoneNumber' => $this->phoneNumber,
            'email'       => $this->email,
            'website'     => $this->website,
            'address'     => $this->address?->toArray(),
            'bankAccount' => $this->bankAccount?->toArray(),
            'sources'     => $this->sources?->toArray(),
            'cache'       => $this->cache,
        ];
    }
}
