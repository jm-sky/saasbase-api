<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Financial\Enums\PaymentStatus;
use App\Domain\Financial\Models\PaymentMethod;
use Brick\Math\BigDecimal;
use Carbon\Carbon;

/**
 * @property PaymentStatus                 $status
 * @property ?Carbon                       $dueDate
 * @property ?Carbon                       $paidDate
 * @property ?BigDecimal                   $paidAmount
 * @property PaymentMethodDTO              $method
 * @property ?string                       $reference
 * @property ?string                       $terms
 * @property ?string                       $notes
 * @property ?InvoicePaymentBankAccountDTO $bankAccount
 */
final class InvoicePaymentDTO extends BaseDataDTO
{
    public function __construct(
        public PaymentStatus $status,
        public PaymentMethodDTO $method,
        public ?Carbon $dueDate = null,
        public ?Carbon $paidDate = null,
        public ?BigDecimal $paidAmount = null,
        public ?string $reference = null,
        public ?string $terms = null,
        public ?string $notes = null,
        public ?InvoicePaymentBankAccountDTO $bankAccount = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'status'      => $this->status->value,
            'dueDate'     => $this->dueDate?->format('Y-m-d\TH:i:s.u\Z'),
            'paidDate'    => $this->paidDate?->format('Y-m-d\TH:i:s.u\Z'),
            'paidAmount'  => $this->paidAmount?->toFloat(),
            'method'      => $this->method->toArray(),
            'reference'   => $this->reference,
            'terms'       => $this->terms,
            'notes'       => $this->notes,
            'bankAccount' => $this->bankAccount?->toArray(),
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            status: PaymentStatus::from($data['status']),
            dueDate: isset($data['dueDate']) ? Carbon::parse($data['dueDate']) : null,
            paidDate: isset($data['paidDate']) ? Carbon::parse($data['paidDate']) : null,
            paidAmount: isset($data['paidAmount']) ? BigDecimal::of($data['paidAmount']) : null,
            method: PaymentMethodDTO::fromArray($data['method']),
            reference: isset($data['reference']) ? $data['reference'] : null,
            terms: isset($data['terms']) ? $data['terms'] : null,
            notes: $data['notes'] ?? null,
            bankAccount: isset($data['bankAccount']) ? InvoicePaymentBankAccountDTO::fromArray($data['bankAccount']) : null,
        );
    }

    public static function default(?PaymentMethod $method = null): static
    {
        return new self(
            status: PaymentStatus::PENDING,
            method: PaymentMethodDTO::default($method),
        );
    }
}
