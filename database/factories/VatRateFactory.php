<?php

namespace Database\Factories;

use App\Domain\Common\Models\VatRate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Common\Models\VatRate>
 */
class VatRateFactory extends Factory
{
    protected $model = VatRate::class;

    public function definition(): array
    {
        return [
            'id'   => Str::ulid()->toString(),
            'name' => fake()->randomElement(['Standard', 'Reduced', 'Zero', 'Exempt']),
            'rate' => fake()->randomElement([0, 5, 8, 23]),
        ];
    }
}
