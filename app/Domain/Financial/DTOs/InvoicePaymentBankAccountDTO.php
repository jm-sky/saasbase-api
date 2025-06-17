<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class InvoicePaymentBankAccountDTO extends BaseDataDTO
{
    public function __construct(
        public ?string $iban = null,
        public ?string $country = null,
        public ?string $swift = null,
        public ?string $bankName = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'iban'     => $this->iban,
            'country'  => $this->country,
            'swift'    => $this->swift,
            'bankName' => $this->bankName,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            iban: $data['iban'] ?? null,
            country: $data['country'] ?? null,
            swift: $data['swift'] ?? null,
            bankName: $data['bankName'] ?? null,
        );
    }
}
