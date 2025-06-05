<?php

namespace Database\Factories;

use App\Domain\Common\Models\MeasurementUnit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MeasurementUnit>
 */
class MeasurementUnitFactory extends Factory
{
    protected $model = MeasurementUnit::class;

    public function definition(): array
    {
        return [
            'id'       => Str::ulid()->toString(),
            'code'     => strtoupper($this->faker->unique()->lexify('??')),
            'name'     => $this->faker->randomElement(['Piece', 'Kilogram', 'Meter', 'Hour', 'Box', 'Set', 'Pack']),
            'category' => $this->faker->randomElement(['quantity', 'length', 'weight', 'time', 'volume', 'area', 'energy']),
        ];
    }
}
