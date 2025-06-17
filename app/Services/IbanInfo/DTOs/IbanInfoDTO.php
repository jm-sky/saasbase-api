<?php

namespace App\Services\IbanInfo\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class IbanInfoDTO extends BaseDataDTO
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

    public static function fromArray(array $data): static
    {
        return new self(
            bankName: $data['bankName'],
            iban: $data['iban'],
            branchName: $data['branchName'] ?? null,
            swift: $data['swift'] ?? null,
            bankCode: $data['bankCode'] ?? null,
            routingCode: $data['routingCode'] ?? null,
            currency: $data['currency'] ?? null,
        );
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
