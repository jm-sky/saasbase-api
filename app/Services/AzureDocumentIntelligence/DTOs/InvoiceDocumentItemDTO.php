<?php

namespace App\Services\AzureDocumentIntelligence\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\CurrencyField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\NumberField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\StringField;

/**
 * DTO for Invoice Item (line) from Azure Document Intelligence.
 *
 * @property ?StringField   $description
 * @property ?NumberField   $quantity
 * @property ?CurrencyField $unitPrice
 * @property ?CurrencyField $totalPrice
 * @property ?CurrencyField $amount
 * @property ?CurrencyField $tax
 * @property ?StringField   $taxRate
 * @property ?StringField   $unit
 * @property float          $confidence
 */
class InvoiceDocumentItemDTO extends BaseDataDTO
{
    public function __construct(
        public readonly ?StringField $description,
        public readonly ?NumberField $quantity,
        public readonly ?CurrencyField $unitPrice,
        public readonly ?CurrencyField $totalPrice,
        public readonly ?CurrencyField $amount,
        public readonly ?CurrencyField $tax,
        public readonly ?StringField $taxRate,
        public readonly ?StringField $unit,
        public readonly float $confidence = 1.0,
    ) {
    }

    public static function fromAzureArray(array $data): self
    {
        return new self(
            description: isset($data['Description']) ? StringField::fromArray($data['Description']) : null,
            quantity: isset($data['Quantity']) ? NumberField::fromArray($data['Quantity']) : null,
            unitPrice: isset($data['UnitPrice']) ? CurrencyField::fromArray($data['UnitPrice']) : null,
            totalPrice: isset($data['TotalPrice']) ? CurrencyField::fromArray($data['TotalPrice']) : null,
            amount: isset($data['Amount']) ? CurrencyField::fromArray($data['Amount']) : null,
            tax: isset($data['Tax']) ? CurrencyField::fromArray($data['Tax']) : null,
            taxRate: isset($data['TaxRate']) ? StringField::fromArray($data['TaxRate']) : null,
            unit: isset($data['Unit']) ? StringField::fromArray($data['Unit']) : null,
            confidence: (float) ($data['confidence'] ?? 1.0),
        );
    }

    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'quantity'    => $this->quantity,
            'unitPrice'   => $this->unitPrice?->toArray(),
            'totalPrice'  => $this->totalPrice?->toArray(),
            'amount'      => $this->amount?->toArray(),
            'tax'         => $this->tax?->toArray(),
            'taxRate'     => $this->taxRate,
            'unit'        => $this->unit,
            'confidence'  => $this->confidence,
        ];
    }
}
