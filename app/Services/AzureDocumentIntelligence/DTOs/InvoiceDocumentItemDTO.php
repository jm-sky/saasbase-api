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
final class InvoiceDocumentItemDTO extends BaseDataDTO
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

    public static function fromArray(array $data): static
    {
        return new self(
            description: isset($data['description']) ? StringField::fromArray($data['description']) : null,
            quantity: isset($data['quantity']) ? NumberField::fromArray($data['quantity']) : null,
            unitPrice: isset($data['unitPrice']) ? CurrencyField::fromArray($data['unitPrice']) : null,
            totalPrice: isset($data['totalPrice']) ? CurrencyField::fromArray($data['totalPrice']) : null,
            amount: isset($data['amount']) ? CurrencyField::fromArray($data['amount']) : null,
            tax: isset($data['tax']) ? CurrencyField::fromArray($data['tax']) : null,
            taxRate: isset($data['taxRate']) ? StringField::fromArray($data['taxRate']) : null,
            unit: isset($data['unit']) ? StringField::fromArray($data['unit']) : null,
            confidence: (float) ($data['confidence'] ?? 1.0),
        );
    }

    public static function fromAzureArray(array $data): static
    {
        return new self(
            description: isset($data['Description']) ? StringField::fromAzureArray($data['Description']) : null,
            quantity: isset($data['Quantity']) ? NumberField::fromAzureArray($data['Quantity']) : null,
            unitPrice: isset($data['UnitPrice']) ? CurrencyField::fromAzureArray($data['UnitPrice']) : null,
            totalPrice: isset($data['TotalPrice']) ? CurrencyField::fromAzureArray($data['TotalPrice']) : null,
            amount: isset($data['Amount']) ? CurrencyField::fromAzureArray($data['Amount']) : null,
            tax: isset($data['Tax']) ? CurrencyField::fromAzureArray($data['Tax']) : null,
            taxRate: isset($data['TaxRate']) ? StringField::fromAzureArray($data['TaxRate']) : null,
            unit: isset($data['Unit']) ? StringField::fromAzureArray($data['Unit']) : null,
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
