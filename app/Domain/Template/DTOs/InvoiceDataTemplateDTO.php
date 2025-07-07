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
        public readonly array $items,
        public readonly string $totalNet,
        public readonly string $totalTax,
        public readonly string $totalGross,
        public readonly string $currency,
        public readonly array $payment,
        public readonly array $options = [],
        public readonly ?string $notes = null,
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
            items: $data['items'],
            totalNet: $data['totalNet'],
            totalTax: $data['totalTax'],
            totalGross: $data['totalGross'],
            currency: $data['currency'],
            payment: $data['payment'],
            options: $data['options'] ?? [],
            notes: $data['notes'] ?? null,
            logoUrl: $data['logoUrl'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'number'     => $this->number,
            'issueDate'  => $this->issueDate,
            'dueDate'    => $this->dueDate,
            'seller'     => $this->seller->toArray(),
            'buyer'      => $this->buyer->toArray(),
            'items'      => $this->items,
            'totalNet'   => $this->totalNet,
            'totalTax'   => $this->totalTax,
            'totalGross' => $this->totalGross,
            'currency'   => $this->currency,
            'payment'    => $this->payment,
            'options'    => $this->options,
            'notes'      => $this->notes,
            'logoUrl'    => $this->logoUrl,
        ];
    }
}
