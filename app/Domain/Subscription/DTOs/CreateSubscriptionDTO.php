<?php

namespace App\Domain\Subscription\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Subscription\Enums\BillingInterval;

final class CreateSubscriptionDTO extends BaseDataDTO
{
    public function __construct(
        public string $planId,
        public string $billingCustomerId,
        public BillingInterval $billingInterval,
        public PaymentDetailsDTO $paymentDetails,
        public ?string $paymentBehavior,
        public ?string $trialEndsAt,
        public ?string $couponCode,
        public ?array $metadata = null
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            planId: $data['planId'],
            billingCustomerId: $data['billingCustomerId'],
            billingInterval: $data['billingInterval'],
            paymentDetails: PaymentDetailsDTO::fromArray($data['paymentDetails']),
            paymentBehavior: $data['paymentBehavior'],
            trialEndsAt: $data['trialEndsAt'],
            couponCode: $data['couponCode'],
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'planId'            => $this->planId,
            'billingCustomerId' => $this->billingCustomerId,
            'billingInterval'   => $this->billingInterval,
            'paymentDetails'    => $this->paymentDetails->toArray(),
            'paymentBehavior'   => $this->paymentBehavior,
            'trialEndsAt'       => $this->trialEndsAt,
            'couponCode'        => $this->couponCode,
            'metadata'          => $this->metadata ?? [],
        ];
    }
}
