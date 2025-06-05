<?php

namespace Database\Factories;

use App\Domain\Auth\Models\User;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ProjectStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'id'          => Str::ulid(),
            'tenant_id'   => Tenant::factory(),
            'name'        => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status_id'   => ProjectStatus::factory(),
            'owner_id'    => User::factory(),
            'start_date'  => fake()->dateTimeBetween('-1 year', 'now'),
            'end_date'    => fake()->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
