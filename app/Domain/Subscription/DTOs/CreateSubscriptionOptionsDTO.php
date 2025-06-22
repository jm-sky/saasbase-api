<?php

namespace App\Domain\Subscription\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Subscription\Enums\BillingInterval;
use Carbon\Carbon;

final class CreateSubscriptionOptionsDTO extends BaseDataDTO
{
    public function __construct(
        public ?BillingInterval $billingInterval = null,
        public ?Carbon $trialEndsAt = null,
        public ?string $paymentBehavior = null,
        public ?array $metadata = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            billingInterval: BillingInterval::from($data['billingInterval'] ?? null),
            trialEndsAt: Carbon::parse($data['trialEndsAt'] ?? null),
            paymentBehavior: $data['paymentBehavior'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'billingInterval' => $this->billingInterval,
            'trialEndsAt'     => $this->trialEndsAt->toDateString(),
            'paymentBehavior' => $this->paymentBehavior,
            'metadata'        => $this->metadata,
        ];
    }
}
