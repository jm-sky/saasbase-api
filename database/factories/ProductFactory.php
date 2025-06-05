<?php

namespace Database\Factories;

use App\Domain\Common\Models\MeasurementUnit;
use App\Domain\Common\Models\VatRate;
use App\Domain\Products\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Products\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'id'          => Str::ulid(),
            'name'        => fake()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'unit_id'     => fn (array $attributes) => $attributes['tenant_id'] ? MeasurementUnit::factory(['tenant_id' => $attributes['tenant_id']]) : null,
            'price_net'   => fake()->randomFloat(2, 10, 1000),
            'vat_rate_id' => null, // VatRate::factory(),
        ];
    }
}
