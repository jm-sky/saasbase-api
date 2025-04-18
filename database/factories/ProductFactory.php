<?php

namespace Database\Factories;

use App\Domain\Common\Models\MeasurementUnit;
use App\Domain\Common\Models\VatRate;
use App\Domain\Products\Models\Product;
use App\Domain\Tenant\Models\Tenant;
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
            'id'          => fake()->uuid(),
            'tenant_id'   => Tenant::factory(),
            'name'        => fake()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'unit_id'     => MeasurementUnit::factory(),
            'price_net'   => fake()->randomFloat(2, 10, 1000),
            'vat_rate_id' => VatRate::factory(),
        ];
    }
}
