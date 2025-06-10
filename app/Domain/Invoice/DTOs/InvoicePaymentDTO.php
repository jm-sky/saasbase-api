<?php

namespace App\Domain\Invoice\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Invoice\Enums\InvoicePaymentMethod;
use App\Domain\Invoice\Enums\InvoicePaymentStatus;
use Brick\Math\BigDecimal;
use Carbon\Carbon;

/**
 * @property InvoicePaymentStatus $status
 * @property Carbon               $dueDate
 * @property ?Carbon              $paidDate
 * @property BigDecimal           $paidAmount
 * @property InvoicePaymentMethod $method
 * @property string               $reference
 * @property string               $terms
 * @property ?string              $notes
 */
class InvoicePaymentDTO extends BaseDataDTO
{
    public function __construct(
        public InvoicePaymentStatus $status,
        public Carbon $dueDate,
        public ?Carbon $paidDate,
        public BigDecimal $paidAmount,
        public InvoicePaymentMethod $method,
        public string $reference,
        public string $terms,
        public ?string $notes = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'status'     => $this->status->value,
            'dueDate'    => $this->dueDate->format('Y-m-d\TH:i:s.u\Z'),
            'paidDate'   => $this->paidDate?->format('Y-m-d\TH:i:s.u\Z'),
            'paidAmount' => $this->paidAmount->toFloat(),
            'method'     => $this->method->value,
            'reference'  => $this->reference,
            'terms'      => $this->terms,
            'notes'      => $this->notes,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            status: InvoicePaymentStatus::from($data['status']),
            dueDate: Carbon::parse($data['dueDate']),
            paidDate: isset($data['paidDate']) ? Carbon::parse($data['paidDate']) : null,
            paidAmount: new BigDecimal($data['paidAmount']),
            method: InvoicePaymentMethod::from($data['method']),
            reference: $data['reference'],
            terms: $data['terms'],
            notes: $data['notes'] ?? null,
        );
    }
}
