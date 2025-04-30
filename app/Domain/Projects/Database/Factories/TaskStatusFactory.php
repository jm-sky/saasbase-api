<?php

namespace App\Domain\Projects\Database\Factories;

use App\Domain\Projects\Models\TaskStatus;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TaskStatus>
 */
class TaskStatusFactory extends Factory
{
    protected $model = TaskStatus::class;

    public function definition(): array
    {
        return [
            'id'          => Str::uuid(),
            'tenant_id'   => Tenant::factory(),
            'name'        => fake()->words(2, true),
            'color'       => fake()->hexColor(),
            'sort_order'  => fake()->numberBetween(1, 100),
            'is_default'  => false,
        ];
    }

    public function default(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
