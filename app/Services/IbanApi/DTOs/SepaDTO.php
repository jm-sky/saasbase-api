<?php

namespace App\Services\IbanApi\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class SepaDTO extends BaseDataDTO
{
    public function __construct(
        public string $sepa_credit_transfer,
        public string $sepa_credit_transfer_inst,
        public string $sepa_direct_debit,
        public string $sepa_sdd_core,
        public string $sepa_b2b,
        public string $sepa_card_clearing,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            sepa_credit_transfer: $data['sepa_credit_transfer'],
            sepa_credit_transfer_inst: $data['sepa_credit_transfer_inst'],
            sepa_direct_debit: $data['sepa_direct_debit'],
            sepa_sdd_core: $data['sepa_sdd_core'],
            sepa_b2b: $data['sepa_b2b'],
            sepa_card_clearing: $data['sepa_card_clearing'],
        );
    }

    public function toArray(): array
    {
        return [
            'sepa_credit_transfer'      => $this->sepa_credit_transfer,
            'sepa_credit_transfer_inst' => $this->sepa_credit_transfer_inst,
            'sepa_direct_debit'         => $this->sepa_direct_debit,
            'sepa_sdd_core'             => $this->sepa_sdd_core,
            'sepa_b2b'                  => $this->sepa_b2b,
            'sepa_card_clearing'        => $this->sepa_card_clearing,
        ];
    }
}
