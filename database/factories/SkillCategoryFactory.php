<?php

namespace Database\Factories;

use App\Domain\Skills\Models\SkillCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillCategoryFactory extends Factory
{
    protected $model = SkillCategory::class;

    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
        ];
    }
}
