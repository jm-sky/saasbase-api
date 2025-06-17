<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * @property InvoiceLineDTO[]       $lines
 * @property InvoiceVatSummaryDTO[] $vatSummary
 * @property InvoiceExchangeDTO     $exchange
 * @property ?string                $description
 */
final class InvoiceBodyDTO extends BaseDataDTO
{
    public function __construct(
        /** @var InvoiceLineDTO[] */
        public array $lines,
        /** @var InvoiceVatSummaryDTO[] */
        public array $vatSummary,
        public InvoiceExchangeDTO $exchange,
        public ?string $description,
    ) {
    }

    public function toArray(): array
    {
        return [
            'lines'       => array_map(fn (InvoiceLineDTO $line) => $line->toArray(), $this->lines),
            'vatSummary'  => array_map(fn (InvoiceVatSummaryDTO $summary) => $summary->toArray(), $this->vatSummary),
            'exchange'    => $this->exchange->toArray(),
            'description' => $this->description,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            lines: array_map(fn (array $line) => InvoiceLineDTO::fromArray($line), $data['lines']),
            vatSummary: isset($data['vatSummary']) ? array_map(fn (array $summary) => InvoiceVatSummaryDTO::fromArray($summary), $data['vatSummary']) : [],
            exchange: InvoiceExchangeDTO::fromArray($data['exchange']),
            description: $data['description'] ?? null,
        );
    }
}
