<?php

namespace Database\Factories;

use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\SkillCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'category_id' => SkillCategory::factory(),
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
        ];
    }
}
