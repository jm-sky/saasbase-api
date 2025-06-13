<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Invoice\Models\Invoice;

/**
 * @property string        $id
 * @property string        $tenantId
 * @property InvoiceType   $type
 * @property InvoiceStatus $status
 * @property string        $number
 * @property string        $numberingTemplateId
 * @property float         $totalNet
 * @property float         $totalTax
 * @property float         $totalGross
 * @property string        $currency
 * @property float         $exchangeRate
 * @property array         $seller
 * @property array         $buyer
 * @property array         $body
 * @property array         $payment
 * @property array         $options
 * @property string        $issueDate
 * @property string        $createdAt
 * @property string        $updatedAt
 * @property array         $numberingTemplate
 */
class InvoiceDTO extends BaseDataDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public readonly InvoiceType $type,
        public readonly InvoiceStatus $status,
        public readonly string $number,
        public readonly string $numberingTemplateId,
        public readonly float $totalNet,
        public readonly float $totalTax,
        public readonly float $totalGross,
        public readonly string $currency,
        public readonly float $exchangeRate,
        public readonly array $seller,
        public readonly array $buyer,
        public readonly array $body,
        public readonly array $payment,
        public readonly array $options,
        public readonly ?string $issueDate = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
        public readonly ?array $numberingTemplate = null,
    ) {
    }

    public static function from(Invoice $invoice): self
    {
        return new self(
            id: $invoice->id,
            tenantId: $invoice->tenant_id,
            type: $invoice->type,
            status: $invoice->status,
            number: $invoice->number,
            numberingTemplateId: $invoice->numbering_template_id,
            totalNet: $invoice->total_net->toFloat(),
            totalTax: $invoice->total_tax->toFloat(),
            totalGross: $invoice->total_gross->toFloat(),
            currency: $invoice->currency,
            exchangeRate: $invoice->exchange_rate->toFloat(),
            seller: $invoice->seller->toArray(),
            buyer: $invoice->buyer->toArray(),
            body: $invoice->body->toArray(),
            payment: $invoice->payment->toArray(),
            options: $invoice->options->toArray(),
            issueDate: $invoice->issue_date?->toDateString(),
            createdAt: $invoice->created_at?->toIso8601String(),
            updatedAt: $invoice->updated_at?->toIso8601String(),
            numberingTemplate: $invoice->numberingTemplate?->toArray(),
        );
    }

    public function toArray(): array
    {
        return [
            'id'                    => $this->id,
            'tenant_id'             => $this->tenantId,
            'type'                  => $this->type->value,
            'status'                => $this->status->value,
            'number'                => $this->number,
            'numbering_template_id' => $this->numberingTemplateId,
            'total_net'             => $this->totalNet,
            'total_tax'             => $this->totalTax,
            'total_gross'           => $this->totalGross,
            'currency'              => $this->currency,
            'exchange_rate'         => $this->exchangeRate,
            'seller'                => $this->seller,
            'buyer'                 => $this->buyer,
            'body'                  => $this->body,
            'payment'               => $this->payment,
            'options'               => $this->options,
            'issue_date'            => $this->issueDate,
            'created_at'            => $this->createdAt,
            'updated_at'            => $this->updatedAt,
        ];
    }
}
