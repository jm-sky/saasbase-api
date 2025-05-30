<?php

namespace App\Domain\Bank\DTO;

use App\Domain\Common\DTOs\BaseDataDTO;

class BankInfoDTO extends BaseDataDTO
{
    public function __construct(
        public string $bankName,
        public string $iban,
        public ?string $branchName,
        public ?string $swift,
        public ?string $bankCode,
        public ?string $routingCode,
        public ?string $currency = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'iban'        => $this->iban,
            'bankName'    => $this->bankName,
            'branchName'  => $this->branchName,
            'swift'       => $this->swift,
            'bankCode'    => $this->bankCode,
            'routingCode' => $this->routingCode,
            'currency'    => $this->currency,
        ];
    }
}
