<?php

namespace App\Services\AzureDocumentIntelligence\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * DTO for Invoice Payment Detail from Azure Document Intelligence.
 *
 * @property string $iban
 * @property string $swift
 * @property float  $confidence
 */
class InvoiceDocumentPaymentDetailDTO extends BaseDataDTO
{
    public function __construct(
        public readonly string $iban,
        public readonly string $swift,
        public readonly float $confidence = 1.0,
    ) {
    }

    public static function fromAzureArray(array $data): self
    {
        return new self(
            iban: (string) ($data['IBAN']['valueString'] ?? ''),
            swift: (string) ($data['SWIFT']['valueString'] ?? ''),
            confidence: (float) ($data['confidence'] ?? 1.0),
        );
    }

    public function toArray(): array
    {
        return [
            'iban'       => $this->iban,
            'swift'      => $this->swift,
            'confidence' => $this->confidence,
        ];
    }
}
