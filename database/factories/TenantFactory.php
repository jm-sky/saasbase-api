<?php

namespace Database\Factories;

use App\Domains\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
        ];
    }
}
