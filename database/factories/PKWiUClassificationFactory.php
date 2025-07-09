<?php

namespace Database\Factories;

use App\Domain\Financial\Models\PKWiUClassification;
use Illuminate\Database\Eloquent\Factories\Factory;

class PKWiUClassificationFactory extends Factory
{
    protected $model = PKWiUClassification::class;

    public function definition(): array
    {
        return [
            'code'        => $this->faker->regexify('[0-9]{2}\.[0-9]{2}\.[0-9]{2}\.[0-9]'),
            'parent_code' => null,
            'name'        => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'level'       => $this->faker->numberBetween(1, 4),
            'is_active'   => true,
        ];
    }

    public function level(int $level): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $level,
        ]);
    }

    public function withParent(string $parentCode): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_code' => $parentCode,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
