<?php

namespace Database\Factories;

use App\Domain\Common\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Common\Models\Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'code' => strtoupper(fake()->unique()->lexify('??')),
            'name' => fake()->randomElement(['Piece', 'Kilogram', 'Meter', 'Hour', 'Box', 'Set', 'Pack']),
        ];
    }
}
