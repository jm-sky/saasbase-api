<?php

namespace Database\Factories\DTOs;

use App\Domain\Invoice\DTOs\InvoicePaymentDTO;
use App\Domain\Invoice\Enums\InvoicePaymentMethod;
use App\Domain\Invoice\Enums\InvoicePaymentStatus;
use Brick\Math\BigDecimal;
use Carbon\Carbon;

class InvoicePaymentDTOFactory extends DTOFactory
{
    public function make(): InvoicePaymentDTO
    {
        return new InvoicePaymentDTO(
            status: InvoicePaymentStatus::PENDING,
            dueDate: Carbon::now()->addDays(14),
            paidDate: null,
            paidAmount: BigDecimal::of('0'),
            method: fake()->randomElement(InvoicePaymentMethod::cases()),
            reference: fake()->numerify('PAY-####'),
            terms: 'Net 14',
            notes: fake()->optional()->sentence(),
        );
    }

    public function paid(): InvoicePaymentDTO
    {
        return new InvoicePaymentDTO(
            status: InvoicePaymentStatus::PAID,
            dueDate: Carbon::now()->addDays(14),
            paidDate: Carbon::now(),
            paidAmount: BigDecimal::of(fake()->randomFloat(2, 100, 1000)),
            method: fake()->randomElement(InvoicePaymentMethod::cases()),
            reference: fake()->numerify('PAY-####'),
            terms: 'Net 14',
            notes: fake()->optional()->sentence(),
        );
    }
}
