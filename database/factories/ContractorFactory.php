<?php

namespace Database\Factories;

use App\Domain\Contractors\Models\Contractor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Contractors\Models\Contractor>
 */
class ContractorFactory extends Factory
{
    protected $model = Contractor::class;

    public function definition(): array
    {
        return [
            'id'          => fake()->uuid(),
            'name'        => fake()->company(),
            'tax_id'      => fake()->numerify('##########'),
            'email'       => fake()->companyEmail(),
            'phone'       => fake()->phoneNumber(),
            'country'     => fake()->country(),
            'website'     => fake()->optional()->url(),
            'description' => fake()->optional()->text(),
            'is_active'   => fake()->boolean(80), // 80% chance of being active
            'is_buyer'    => fake()->boolean(80),
            'is_supplier' => fake()->boolean(80),
        ];
    }
}
