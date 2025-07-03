<?php

namespace Database\Factories;

use App\Domain\Contractors\Models\Contractor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Contractors\Models\Contractor>
 */
class ContractorFactory extends Factory
{
    protected $model = Contractor::class;

    public function definition(): array
    {
        return [
            'id'          => Str::ulid()->toString(),
            'name'        => fake()->company(),
            'tax_id'      => fake()->numerify('##########'),
            'email'       => fake()->companyEmail(),
            'phone'       => fake()->phoneNumber(),
            'country'     => fake()->countryCode(),
            'website'     => fake()->optional()->url(),
            'description' => fake()->optional()->text(),
            'is_active'   => fake()->boolean(80), // 80% chance of being active
            'is_buyer'    => fake()->boolean(80),
            'is_supplier' => fake()->boolean(80),
        ];
    }

    public function bp(): self
    {
        return $this->state(fn (array $attributes) => [
            'name'        => 'BP Polska Sp. z o.o.',
            'tax_id'      => 'PL9720865431',
            'email'       => 'bp@bp.com',
            'phone'       => '+48 12 345 67 89',
            'country'     => 'PL',
            'website'     => 'https://www.bp.com',
            'description' => 'BP Polska Sp. z o.o. is a Polish company that provides energy services.',
            'is_active'   => true,
        ]);
    }

    public function nasa(): self
    {
        return $this->state(fn (array $attributes) => [
            'name'        => 'National Aeronautics and Space Administration',
            'tax_id'      => 'US-NASA-2024',
            'email'       => 'nasa@nasa.gov',
            'phone'       => '+1 202 358 0001',
            'country'     => 'US',
            'website'     => 'https://www.nasa.gov',
            'description' => 'National Aeronautics and Space Administration is a US government agency that provides space services.',
            'is_active'   => true,
        ]);
    }
}
