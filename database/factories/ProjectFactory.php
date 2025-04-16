<?php

namespace Database\Factories;

use App\Domain\Projects\Models\Project;
use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'tenant_id' => Tenant::factory(),
            'name' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['active', 'completed', 'archived']),
            'owner_id' => User::factory(),
            'start_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'end_date' => fake()->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
