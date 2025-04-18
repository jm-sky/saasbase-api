<?php

namespace Tests\Feature\Database\Seeders;

use App\Domain\Common\Models\MeasurementUnit;
use Database\Seeders\MeasurementUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeasurementUnitSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_measurement_unit_seeder_creates_expected_records(): void
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
