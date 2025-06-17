<?php

namespace App\Domain\Subscription\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class CheckoutDataDTO extends BaseDataDTO
{
    public function __construct(
        public readonly string $checkoutUrl,
        public readonly string $sessionId,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            checkoutUrl: $data['checkoutUrl'],
            sessionId: $data['sessionId'],
        );
    }

    public function toArray(): array
    {
        return [
            'checkoutUrl' => $this->checkoutUrl,
            'sessionId'   => $this->sessionId,
        ];
    }
}
