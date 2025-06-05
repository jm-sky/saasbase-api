<?php

namespace Database\Factories;

use App\Domain\Skills\Models\SkillCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SkillCategoryFactory extends Factory
{
    protected $model = SkillCategory::class;

    public function definition(): array
    {
        return [
            'id'          => Str::ulid()->toString(),
            'name'        => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'created_at'  => fake()->dateTime(),
        ];
    }
}
