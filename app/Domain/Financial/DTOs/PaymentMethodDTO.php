<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Financial\Enums\PaymentMethodCode;
use App\Domain\Financial\Models\PaymentMethod;

final class PaymentMethodDTO extends BaseDataDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $id = null,
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
            $data['name'] ?? null,
            $data['id'] ?? null,
            $data['paymentDays'] ?? null,
        );
    }

    public static function default(?PaymentMethod $method = null): static
    {
        $method = $method ?? PaymentMethod::firstWhere('code', PaymentMethodCode::BankTransfer->value);

        return new self(
            name: $method->name,
            id: $method->id,
            paymentDays: $method->payment_days,
        );
    }
}
