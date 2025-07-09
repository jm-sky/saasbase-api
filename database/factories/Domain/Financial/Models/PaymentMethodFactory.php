<?php

namespace Database\Factories\Domain\Financial\Models;

use App\Domain\Financial\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'tenant_id'    => Str::ulid(),
            'name'         => $this->faker->word,
            'payment_days' => $this->faker->optional()->numberBetween(0, 60),
        ];
    }
}
