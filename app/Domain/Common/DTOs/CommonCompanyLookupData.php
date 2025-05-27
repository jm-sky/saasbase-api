<?php

namespace App\Domain\Common\DTOs;

/**
 * @property string          $name
 * @property string          $country
 * @property ?string         $vatId
 * @property ?string         $regon
 * @property ?string         $shortName
 * @property ?string         $phoneNumber
 * @property ?string         $email
 * @property ?string         $website
 * @property ?AddressDTO     $address
 * @property ?BankAccountDTO $bankAccount
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
    ) {
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
        ];
    }
}
