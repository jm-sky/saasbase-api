<?php

namespace App\Domain\Subscription\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * Carries a Stripe PaymentMethod token created client-side via Stripe.js/Elements.
 * Raw card number/CVC must never be sent to or handled by this backend (PCI-DSS
 * SAQ A vs SAQ D) — the frontend tokenizes the card directly with Stripe and only
 * the resulting `pm_...` id is transmitted here.
 */
final class PaymentDetailsDTO extends BaseDataDTO
{
    public function __construct(
        public string $paymentMethodId,
        public string $name,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            $data['paymentMethodId'],
            $data['name'],
        );
    }

    public function toArray(): array
    {
        return [
            'paymentMethodId' => $this->paymentMethodId,
            'name' => $this->name,
        ];
    }
}
