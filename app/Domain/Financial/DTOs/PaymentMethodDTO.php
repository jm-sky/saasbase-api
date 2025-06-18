<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Financial\Models\PaymentMethod;

final class PaymentMethodDTO extends BaseDataDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?int $paymentDays = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'paymentDays' => $this->paymentDays,
        ];
    }

    public static function fromModel(PaymentMethod $model): static
    {
        return new self(
            $model->id,
            $model->name,
            $model->payment_days,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            $data['id'],
            $data['name'],
            $data['paymentDays'] ?? null,
        );
    }
}
