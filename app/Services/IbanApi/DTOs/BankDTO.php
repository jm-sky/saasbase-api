<?php

namespace App\Services\IbanApi\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class BankDTO extends BaseDataDTO
{
    public function __construct(
        public string $bank_name,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $bic = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $zip = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            bank_name: $data['bank_name'],
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            bic: $data['bic'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            zip: $data['zip'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'bank_name' => $this->bank_name,
            'phone'     => $this->phone ?? null,
            'address'   => $this->address ?? null,
            'bic'       => $this->bic ?? null,
            'city'      => $this->city ?? null,
            'state'     => $this->state ?? null,
            'zip'       => $this->zip ?? null,
        ];
    }
}
