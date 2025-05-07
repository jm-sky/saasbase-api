<?php

namespace Tests\Feature\Database\Seeders;

use Database\Seeders\DefaultMeasurementUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversNothing]
class DefaultMeasurementUnitSeederTest extends TestCase
{
    use RefreshDatabase;

    public function testMeasurementUnitSeederCreatesExpectedRecords(): void
    {
        $this->seed(DefaultMeasurementUnitSeeder::class);

        $this->assertDatabaseCount('default_measurement_units', 26);

        // Test some basic units
        $this->assertDatabaseHas('default_measurement_units', [
            'code' => 'h',
            'name' => 'Hour',
        ]);

        $this->assertDatabaseHas('default_measurement_units', [
            'code' => 'd',
            'name' => 'Day',
        ]);

        $this->assertDatabaseHas('default_measurement_units', [
            'code' => 'pcs',
            'name' => 'Piece',
        ]);

        // Test some measurement units
        $this->assertDatabaseHas('default_measurement_units', [
            'code' => 'kg',
            'name' => 'Kilogram',
        ]);

        $this->assertDatabaseHas('default_measurement_units', [
            'code' => 'l',
            'name' => 'Liter',
        ]);
    }
}
