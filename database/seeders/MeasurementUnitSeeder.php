<?php

namespace Database\Seeders;

use App\Domain\Common\Models\MeasurementUnit;
use Illuminate\Database\Seeder;

class MeasurementUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            [
                'code' => 'h',
                'name' => 'Hour',
            ],
            [
                'code' => 'd',
                'name' => 'Day',
            ],
            [
                'code' => 'pcs',
                'name' => 'Piece',
            ],
            [
                'code' => 'kg',
                'name' => 'Kilogram',
            ],
            [
                'code' => 'g',
                'name' => 'Gram',
            ],
            [
                'code' => 'm',
                'name' => 'Meter',
            ],
            [
                'code' => 'km',
                'name' => 'Kilometer',
            ],
            [
                'code' => 'l',
                'name' => 'Liter',
            ],
            [
                'code' => 'ml',
                'name' => 'Milliliter',
            ],
        ];

        foreach ($units as $unit) {
            MeasurementUnit::create($unit);
        }
    }
}
