<?php

namespace App\Services\IbanApi\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

class BankDTO extends BaseDataDTO
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
