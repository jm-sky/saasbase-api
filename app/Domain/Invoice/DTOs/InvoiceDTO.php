<?php

namespace App\Domain\Invoice\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Invoice\Enums\InvoiceType;
use App\Domain\Invoice\Models\Invoice;
use Illuminate\Support\Collection;

/**
 * @property string      $id
 * @property string      $tenantId
 * @property InvoiceType $type
 * @property string      $status
 * @property string      $number
 * @property string      $numberingTemplateId
 * @property float       $totalNet
 * @property float       $totalTax
 * @property float       $totalGross
 * @property string      $currency
 * @property float       $exchangeRate
 * @property array       $seller
 * @property array       $buyer
 * @property array       $data
 * @property array       $payment
 * @property array       $options
 * @property string      $issueDate
 * @property string      $createdAt
 * @property string      $updatedAt
 * @property array       $numberingTemplate
 */
class InvoiceDTO extends BaseDataDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public readonly InvoiceType $type,
        public readonly string $status,
        public readonly string $number,
        public readonly string $numberingTemplateId,
        public readonly float $totalNet,
        public readonly float $totalTax,
        public readonly float $totalGross,
        public readonly string $currency,
        public readonly float $exchangeRate,
        public readonly array $seller,
        public readonly array $buyer,
        public readonly array $data,
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
            data: $invoice->data->toArray(),
            payment: $invoice->payment->toArray(),
            options: $invoice->options->toArray(),
            issueDate: $invoice->issue_date?->toDateString(),
            createdAt: $invoice->created_at?->toIso8601String(),
            updatedAt: $invoice->updated_at?->toIso8601String(),
            numberingTemplate: $invoice->numberingTemplate?->toArray(),
        );
    }

    public static function collect(Collection $invoices): Collection
    {
        return $invoices->map(fn (Invoice $invoice) => self::from($invoice));
    }

    public function toArray(): array
    {
        return [
            'id'                    => $this->id,
            'tenant_id'             => $this->tenantId,
            'type'                  => $this->type,
            'status'                => $this->status,
            'number'                => $this->number,
            'numbering_template_id' => $this->numberingTemplateId,
            'total_net'             => $this->totalNet,
            'total_tax'             => $this->totalTax,
            'total_gross'           => $this->totalGross,
            'currency'              => $this->currency,
            'exchange_rate'         => $this->exchangeRate,
            'seller'                => $this->seller,
            'buyer'                 => $this->buyer,
            'data'                  => $this->data,
            'payment'               => $this->payment,
            'options'               => $this->options,
            'issue_date'            => $this->issueDate,
            'created_at'            => $this->createdAt,
            'updated_at'            => $this->updatedAt,
        ];
    }
}
