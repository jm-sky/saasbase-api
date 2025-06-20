<?php

namespace App\Domain\Subscription\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class PaymentDetailsDTO extends BaseDataDTO
{
    public function __construct(
        public string $cardNumber,
        public string $expiry,
        public string $cvc,
        public string $name,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['cardNumber'],
            $data['expiry'],
            $data['cvc'],
            $data['name'],
        );
    }

    public function toArray(): array
    {
        return [
            'cardNumber' => $this->cardNumber,
            'expiry'     => $this->expiry,
            'cvc'        => $this->cvc,
            'name'       => $this->name,
        ];
    }
}
