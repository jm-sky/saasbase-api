<?php

namespace Database\Factories;

use App\Domain\Common\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        return [
            'name'            => fake()->country(),
            'code'            => strtoupper(fake()->unique()->lexify('??')),
            'code3'           => strtoupper(fake()->unique()->lexify('???')),
            'numeric_code'    => fake()->unique()->numerify('###'),
            'phone_code'      => fake()->numerify('##'),
            'capital'         => fake()->city(),
            'currency_code'   => strtoupper(fake()->lexify('???')),
            'currency_symbol' => fake()->randomElement(['$', '€', '£', '¥']),
            'tld'             => '.' . fake()->lexify('??'),
            'native'          => fake()->country(),
            'region'          => fake()->randomElement(['Europe', 'Asia', 'Africa', 'Americas', 'Oceania']),
            'subregion'       => fake()->word(),
            'emoji'           => '🏳️',
            'emojiU'          => 'U+1F3F3',
        ];
    }
}
