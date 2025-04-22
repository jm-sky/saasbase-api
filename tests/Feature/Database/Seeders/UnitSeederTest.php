<?php

namespace Tests\Feature\Database\Seeders;

use Database\Seeders\MeasurementUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class MeasurementUnitSeederTest extends TestCase
{
    use RefreshDatabase;

    public function testMeasurementUnitSeederCreatesExpectedRecords(): void
    {
        $this->seed(MeasurementUnitSeeder::class);

        $this->assertDatabaseCount('measurement_units', 9);

        // Test some basic units
        $this->assertDatabaseHas('measurement_units', [
            'code' => 'h',
            'name' => 'Hour',
        ]);

        $this->assertDatabaseHas('measurement_units', [
            'code' => 'd',
            'name' => 'Day',
        ]);

        $this->assertDatabaseHas('measurement_units', [
            'code' => 'pcs',
            'name' => 'Piece',
        ]);

        // Test some measurement units
        $this->assertDatabaseHas('measurement_units', [
            'code' => 'kg',
            'name' => 'Kilogram',
        ]);

        $this->assertDatabaseHas('measurement_units', [
            'code' => 'l',
            'name' => 'Liter',
        ]);
    }
}
