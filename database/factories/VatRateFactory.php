<?php

namespace Database\Factories;

use App\Domain\Financial\Enums\VatRateType;
use App\Domain\Financial\Models\VatRate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<VatRate>
 */
class VatRateFactory extends Factory
{
    protected $model = VatRate::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['Standard', 'Reduced', 'Zero', 'Exempt']);
        $rate = $name === 'Zero' ? 0 : fake()->randomElement([5, 8, 23]);

        return [
            'id' => Str::ulid()->toString(),
            'country_code' => 'PL',
            'name' => $name,
            'type' => $rate > 0 ? VatRateType::PERCENTAGE : VatRateType::ZERO_PERCENT,
            'rate' => $rate,
        ];
    }
}
