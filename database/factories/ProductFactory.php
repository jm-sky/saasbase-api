<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Enums\ProductType;
use App\Domain\Common\Models\MeasurementUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Products\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'id'          => Str::ulid()->toString(),
            'name'        => fake()->words(3, true),
            'type'        => ProductType::PRODUCT,
            'description' => fake()->optional()->paragraph(),
            'unit_id'     => fn (array $attributes) => $attributes['tenant_id'] ? MeasurementUnit::factory(['tenant_id' => $attributes['tenant_id']]) : null,
            'price_net'   => fake()->randomFloat(2, 10, 1000),
            'vat_rate_id' => null, // VatRate::factory(),
        ];
    }
}
