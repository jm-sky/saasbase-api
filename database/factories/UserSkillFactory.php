<?php

namespace Database\Factories;

use App\Domain\Auth\Models\User;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\UserSkill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Skills\Models\UserSkill>
 */
class UserSkillFactory extends Factory
{
    protected $model = UserSkill::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'skill_id'    => Skill::factory(),
            'level'       => fake()->numberBetween(1, 5),
            'acquired_at' => fake()->date(),
        ];
    }
}
