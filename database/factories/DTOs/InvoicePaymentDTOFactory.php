<?php

namespace Database\Factories\DTOs;

use App\Domain\Financial\DTOs\InvoicePaymentDTO;
use App\Domain\Financial\Enums\PaymentMethod;
use App\Domain\Financial\Enums\PaymentStatus;
use Brick\Math\BigDecimal;
use Carbon\Carbon;

class InvoicePaymentDTOFactory extends DTOFactory
{
    public function make(?array $attributes = []): InvoicePaymentDTO
    {
        return new InvoicePaymentDTO(
            status: $attributes['status'] ?? PaymentStatus::PENDING,
            dueDate: $attributes['dueDate'] ?? Carbon::now()->addDays(14),
            paidDate: $attributes['paidDate'] ?? null,
            paidAmount: $attributes['paidAmount'] ?? BigDecimal::of('0'),
            method: $attributes['method'] ?? fake()->randomElement(PaymentMethod::cases()),
            reference: $attributes['reference'] ?? fake()->numerify('PAY-####'),
            terms: $attributes['terms'] ?? 'Net 14',
            notes: $attributes['notes'] ?? fake()->optional()->sentence(),
            bankAccount: $attributes['bankAccount'] ?? (new InvoicePaymentBankAccountDTOFactory())->make(),
        );
    }

    public function paid(?array $attributes = []): InvoicePaymentDTO
    {
        return new InvoicePaymentDTO(
            status: PaymentStatus::PAID,
            dueDate: Carbon::now()->addDays(14),
            paidDate: Carbon::now(),
            paidAmount: BigDecimal::of(fake()->randomFloat(2, 100, 1000)),
            method: fake()->randomElement(PaymentMethod::cases()),
            reference: fake()->numerify('PAY-####'),
            terms: 'Net 14',
            notes: fake()->optional()->sentence(),
            bankAccount: $attributes['bankAccount'] ?? (new InvoicePaymentBankAccountDTOFactory())->make(),
        );
    }
}
