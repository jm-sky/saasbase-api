<?php

namespace Database\Factories\DTOs;

use App\Domain\Financial\DTOs\InvoicePaymentDTO;
use App\Domain\Financial\Enums\PaymentMethod;
use App\Domain\Financial\Enums\PaymentStatus;
use Brick\Math\BigDecimal;
use Carbon\Carbon;

class InvoicePaymentDTOFactory extends DTOFactory
{
    public function make(): InvoicePaymentDTO
    {
        return new InvoicePaymentDTO(
            status: PaymentStatus::PENDING,
            dueDate: Carbon::now()->addDays(14),
            paidDate: null,
            paidAmount: BigDecimal::of('0'),
            method: fake()->randomElement(PaymentMethod::cases()),
            reference: fake()->numerify('PAY-####'),
            terms: 'Net 14',
            notes: fake()->optional()->sentence(),
        );
    }

    public function paid(): InvoicePaymentDTO
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
        );
    }
}
