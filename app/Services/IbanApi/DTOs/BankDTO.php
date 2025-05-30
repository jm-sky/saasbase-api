<?php

namespace App\Services\IbanApi\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

class BankDTO extends BaseDataDTO
{
    public function __construct(
        public string $bank_name,
        public string $phone,
        public string $address,
        public string $bic,
        public string $city,
        public string $state,
        public string $zip,
    ) {
    }

    public function toArray(): array
    {
        return [
            'bank_name' => $this->bank_name,
            'phone'     => $this->phone,
            'address'   => $this->address,
            'bic'       => $this->bic,
            'city'      => $this->city,
            'state'     => $this->state,
            'zip'       => $this->zip,
        ];
    }
}
