<?php

namespace Database\Factories;

use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Models\ContractorContactPerson;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Contractors\Models\ContractorContactPerson>
 */
class ContractorContactPersonFactory extends Factory
{
    protected $model = ContractorContactPerson::class;

    public function definition(): array
    {
        return [
            'id'            => fake()->uuid(),
            'tenant_id'     => Tenant::factory(),
            'contractor_id' => Contractor::factory(),
            'name'          => fake()->name(),
            'email'         => fake()->optional()->email(),
            'phone'         => fake()->optional()->phoneNumber(),
            'position'      => fake()->optional()->jobTitle(),
            'description'   => fake()->optional()->sentence(),
        ];
    }
}
