<?php

namespace Database\Factories\Domain\Utils\Models;

use App\Domain\Utils\Enums\RegistryConfirmationType;
use App\Domain\Utils\Models\RegistryConfirmation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistryConfirmation>
 */
class RegistryConfirmationFactory extends Factory
{
    protected $model = RegistryConfirmation::class;

    public function definition(): array
    {
        return [
            'confirmable_id'   => $this->faker->uuid(),
            'confirmable_type' => 'App\Domain\Contractors\Models\Contractor',
            'type'             => $this->faker->randomElement(RegistryConfirmationType::cases())->value,
            'payload'          => [
                'name'  => $this->faker->company(),
                'vatId' => $this->faker->numerify('##########'),
            ],
            'result' => [
                'registryData' => [
                    'name'  => $this->faker->company(),
                    'vatId' => $this->faker->numerify('##########'),
                ],
                'comparison' => [
                    'nameMatch'  => $this->faker->boolean(),
                    'vatIdMatch' => $this->faker->boolean(),
                ],
            ],
            'success'    => $this->faker->boolean(),
            'checked_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
