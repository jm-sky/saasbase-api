<?php

namespace Database\Factories;

use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Tenant\Models\Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name'        => $name,
            'slug'        => Str::slug($name),
            'owner_id'    => null,
            'tax_id'      => fake()->numerify('##########'),
            'description' => fake()->sentence(),
            'email'       => fake()->email(),
            'phone'       => fake()->phoneNumber(),
            'website'     => fake()->url(),
            'country'     => fake()->country(),
        ];
    }
}
