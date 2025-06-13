<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Financial\Enums\PaymentMethod;
use App\Domain\Financial\Enums\PaymentStatus;
use Brick\Math\BigDecimal;
use Carbon\Carbon;

/**
 * @property PaymentStatus $status
 * @property ?Carbon       $dueDate
 * @property ?Carbon       $paidDate
 * @property ?BigDecimal   $paidAmount
 * @property PaymentMethod $method
 * @property ?string       $reference
 * @property ?string       $terms
 * @property ?string       $notes
 */
class InvoicePaymentDTO extends BaseDataDTO
{
    public function __construct(
        public PaymentStatus $status,
        public ?Carbon $dueDate,
        public ?Carbon $paidDate,
        public ?BigDecimal $paidAmount,
        public PaymentMethod $method = PaymentMethod::BANK_TRANSFER,
        public ?string $reference = null,
        public ?string $terms = null,
        public ?string $notes = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'status'     => $this->status->value,
            'dueDate'    => $this->dueDate?->format('Y-m-d\TH:i:s.u\Z'),
            'paidDate'   => $this->paidDate?->format('Y-m-d\TH:i:s.u\Z'),
            'paidAmount' => $this->paidAmount?->toFloat(),
            'method'     => $this->method->value,
            'reference'  => $this->reference,
            'terms'      => $this->terms,
            'notes'      => $this->notes,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            status: PaymentStatus::from($data['status']),
            dueDate: isset($data['dueDate']) ? Carbon::parse($data['dueDate']) : null,
            paidDate: isset($data['paidDate']) ? Carbon::parse($data['paidDate']) : null,
            paidAmount: isset($data['paidAmount']) ? BigDecimal::of($data['paidAmount']) : null,
            method: PaymentMethod::from($data['method']),
            reference: isset($data['reference']) ? $data['reference'] : null,
            terms: isset($data['terms']) ? $data['terms'] : null,
            notes: $data['notes'] ?? null,
        );
    }
}
