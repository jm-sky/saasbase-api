<?php

namespace Database\Factories;

use App\Domain\Common\Models\BankAccount;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Common\Models\BankAccount>
 */
class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition(): array
    {
        return [
            'id'           => fake()->uuid(),
            'tenant_id'    => Tenant::factory(),
            'iban'         => fake()->iban(),
            'country'      => fake()->countryCode(),
            'swift'        => fake()->optional()->swiftBicNumber(),
            'bank_name'    => fake()->optional()->company(),
            'is_default'   => false,
            'currency'     => fake()->optional()->currencyCode(),
            'description'  => fake()->optional()->sentence(),
        ];
    }

    public function default(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
