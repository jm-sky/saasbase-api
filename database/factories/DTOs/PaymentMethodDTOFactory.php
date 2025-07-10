<?php

namespace Database\Factories\DTOs;

use App\Domain\Financial\DTOs\PaymentMethodDTO;
use App\Domain\Financial\Enums\PaymentMethodCode;
use App\Domain\Financial\Models\PaymentMethod;

class PaymentMethodDTOFactory extends DTOFactory
{
    public function make(?array $attributes = []): PaymentMethodDTO
    {
        /** @var PaymentMethod $method */
        $method = PaymentMethod::firstWhere('code', PaymentMethodCode::BankTransfer->value) ?? PaymentMethod::factory()->create();

        return new PaymentMethodDTO(
            name: $method->name,
            id: $method->id,
            paymentDays: $method->payment_days,
        );
    }
}
