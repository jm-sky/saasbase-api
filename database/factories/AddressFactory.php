<?php

namespace Database\Factories;

use App\Domain\Common\Enums\AddressType;
use App\Domain\Common\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Common\Models\Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id'          => fake()->uuid(),
            'tenant_id'   => fake()->optional()->uuid(),
            'country'     => fake()->countryCode(),
            'postal_code' => fake()->optional()->postcode(),
            'city'        => fake()->city(),
            'street'      => fake()->optional()->streetAddress(),
            'building'    => fake()->optional()->buildingNumber(),
            'flat'        => fake()->optional()->numberBetween(1, 100),
            'description' => fake()->optional()->sentence(),
            'type'        => fake()->randomElement(AddressType::cases()),
            'is_default'  => false,
        ];
    }

    /**
     * Configure the model factory to create a default address.
     */
    public function default(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Configure the model factory to create an address of a specific type.
     */
    public function ofType(AddressType $type): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }
}
