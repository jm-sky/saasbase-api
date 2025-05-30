<?php

namespace App\Services\IbanApi\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

class IbanDataDTO extends BaseDataDTO
{
    public function __construct(
        public string $country_code,
        public string $iso_alpha3,
        public string $country_name,
        public string $currency_code,
        public string $sepa_member,
        public SepaDTO $sepa,
        public string $bban,
        public string $bank_account,
        public BankDTO $bank,
    ) {
    }

    public function toArray(): array
    {
        return [
            'country_code'  => $this->country_code,
            'iso_alpha3'    => $this->iso_alpha3,
            'country_name'  => $this->country_name,
            'currency_code' => $this->currency_code,
            'sepa_member'   => $this->sepa_member,
            'sepa'          => $this->sepa->toArray(),
            'bban'          => $this->bban,
            'bank_account'  => $this->bank_account,
            'bank'          => $this->bank->toArray(),
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            country_code: $data['country_code'],
            iso_alpha3: $data['iso_alpha3'],
            country_name: $data['country_name'],
            currency_code: $data['currency_code'],
            sepa_member: $data['sepa_member'],
            sepa: SepaDTO::fromArray($data['sepa']),
            bban: $data['bban'],
            bank_account: $data['bank_account'],
            bank: BankDTO::fromArray($data['bank']),
        );
    }
}
