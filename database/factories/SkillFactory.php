<?php

namespace Database\Factories;

use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\SkillCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        $category = SkillCategory::factory()->create();

        return [
            'id'          => Str::ulid(),
            'category'    => $category->name,
            'name'        => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'created_at'  => fake()->dateTime(),
        ];
    }
}
