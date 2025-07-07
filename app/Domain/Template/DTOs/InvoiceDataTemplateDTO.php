<?php

namespace App\Domain\Template\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * Template DTO containing all invoice data for template rendering.
 */
final class InvoiceDataTemplateDTO extends BaseDataDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $number,
        public readonly string $issueDate,
        public readonly string $dueDate,
        public readonly InvoicePartyTemplateDTO $seller,
        public readonly InvoicePartyTemplateDTO $buyer,
        public readonly array $lines,
        public readonly array $vatSummary,
        public readonly string $formattedTotalNet,
        public readonly string $formattedTotalTax,
        public readonly string $formattedTotalGross,
        public readonly string $currency,
        public readonly array $payment,
        public readonly array $options = [],
        public readonly ?string $description = null,
        public readonly ?string $logoUrl = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            number: $data['number'],
            issueDate: $data['issueDate'],
            dueDate: $data['dueDate'],
            seller: InvoicePartyTemplateDTO::fromArray($data['seller']),
            buyer: InvoicePartyTemplateDTO::fromArray($data['buyer']),
            lines: $data['lines'],
            vatSummary: $data['vatSummary'],
            formattedTotalNet: $data['formattedTotalNet'],
            formattedTotalTax: $data['formattedTotalTax'],
            formattedTotalGross: $data['formattedTotalGross'],
            currency: $data['currency'],
            payment: $data['payment'],
            options: $data['options'] ?? [],
            description: $data['description'] ?? null,
            logoUrl: $data['logoUrl'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'number'              => $this->number,
            'issueDate'           => $this->issueDate,
            'dueDate'             => $this->dueDate,
            'seller'              => $this->seller->toArray(),
            'buyer'               => $this->buyer->toArray(),
            'lines'               => $this->lines,
            'vatSummary'          => $this->vatSummary,
            'formattedTotalNet'   => $this->formattedTotalNet,
            'formattedTotalTax'   => $this->formattedTotalTax,
            'formattedTotalGross' => $this->formattedTotalGross,
            'currency'            => $this->currency,
            'payment'             => $this->payment,
            'options'             => $this->options,
            'description'         => $this->description,
            'logoUrl'             => $this->logoUrl,
        ];
    }
}
