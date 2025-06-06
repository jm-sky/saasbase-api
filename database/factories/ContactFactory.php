<?php

namespace Database\Factories;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\Contact;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Common\Models\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'id'            => Str::ulid()->toString(),
            'first_name'    => $this->faker->firstName(),
            'last_name'     => $this->faker->lastName(),
            'position'      => $this->faker->optional()->jobTitle(),
            'email'         => $this->faker->unique()->safeEmail(),
            'phone_number'  => $this->faker->optional()->phoneNumber(),
            'emails'        => [$this->faker->safeEmail()],
            'phone_numbers' => [$this->faker->phoneNumber()],
            'notes'         => $this->faker->optional()->sentence(),
            'user_id'       => User::factory(),
            'tenant_id'     => Tenant::factory(),
        ];
    }
}
