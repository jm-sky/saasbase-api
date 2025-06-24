<?php

namespace Database\Factories\Domain\Exchanges;

use App\Domain\Exchanges\Enums\ExchangeRateSource;
use App\Domain\Exchanges\Models\Currency;
use App\Domain\Exchanges\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Exchanges\Models\ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    protected $model = ExchangeRate::class;

    public function definition(): array
    {
        if (0 === Currency::where('code', Currency::POLISH_CURRENCY_CODE)->count()) {
            Currency::factory()->create(['code' => Currency::POLISH_CURRENCY_CODE]);
        }

        return [
            'id'            => Str::ulid()->toString(),
            'base_currency' => Currency::POLISH_CURRENCY_CODE,
            'currency'      => Currency::factory(),
            'date'          => $this->faker->date(),
            'rate'          => $this->faker->randomFloat(6, 0.1, 10),
            'table'         => $this->faker->randomElement(['A', 'B', 'C']),
            'source'        => $this->faker->randomElement(ExchangeRateSource::cases()),
            'created_at'    => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
