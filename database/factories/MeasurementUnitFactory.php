<?php

namespace Database\Factories;

use App\Domain\Common\Models\MeasurementUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeasurementUnit>
 */
class MeasurementUnitFactory extends Factory
{
    protected $model = MeasurementUnit::class;

    public function definition(): array
    {
        return [
            'id'   => $this->faker->uuid(),
            'code' => strtoupper($this->faker->unique()->lexify('??')),
            'name' => $this->faker->randomElement(['Piece', 'Kilogram', 'Meter', 'Hour', 'Box', 'Set', 'Pack']),
        ];
    }
}
