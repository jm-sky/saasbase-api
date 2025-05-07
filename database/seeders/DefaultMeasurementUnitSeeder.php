<?php

namespace Database\Seeders;

use App\Domain\Common\Models\DefaultMeasurementUnit;
use Illuminate\Database\Seeder;

class DefaultMeasurementUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            // Time
            ['code' => 'h',   'name' => 'Hour',        'category' => 'time', 'is_default' => true],
            ['code' => 'min', 'name' => 'Minute',      'category' => 'time'],
            ['code' => 's',   'name' => 'Second',      'category' => 'time'],
            ['code' => 'd',   'name' => 'Day',         'category' => 'time'],
            ['code' => 'wk',  'name' => 'Week',        'category' => 'time'],
            // Length
            ['code' => 'mm',  'name' => 'Millimeter',  'category' => 'length'],
            ['code' => 'cm',  'name' => 'Centimeter',  'category' => 'length'],
            ['code' => 'm',   'name' => 'Meter',       'category' => 'length'],
            ['code' => 'km',  'name' => 'Kilometer',   'category' => 'length'],
            // Mass
            ['code' => 'mg',  'name' => 'Milligram',   'category' => 'mass'],
            ['code' => 'g',   'name' => 'Gram',        'category' => 'mass'],
            ['code' => 'kg',  'name' => 'Kilogram',    'category' => 'mass'],
            // Volume
            ['code' => 'ml',  'name' => 'Milliliter',  'category' => 'volume'],
            ['code' => 'l',   'name' => 'Liter',       'category' => 'volume'],
            // Quantity
            ['code' => 'pcs', 'name' => 'Piece',       'category' => 'quantity', 'is_default' => true],
            ['code' => 'set', 'name' => 'Set',         'category' => 'quantity', 'is_default' => true],
            // Area
            ['code' => 'm2',  'name' => 'Square Meter',    'category' => 'area'],
            ['code' => 'km2', 'name' => 'Square Kilometer', 'category' => 'area'],
            // Energy
            ['code' => 'kwh', 'name' => 'Kilowatt-hour', 'category' => 'energy'],
            ['code' => 'j',   'name' => 'Joule',        'category' => 'energy'],
            // Service/Work
            ['code' => 'md',   'name' => 'Man-day',      'category' => 'service', 'is_default' => true],
            ['code' => 'mh',   'name' => 'Man-hour',     'category' => 'service', 'is_default' => true],
            ['code' => 'unit', 'name' => 'Unit',         'category' => 'quantity', 'is_default' => true],
            // Packaging
            ['code' => 'box',  'name' => 'Box',        'category' => 'packaging'],
            ['code' => 'sbox', 'name' => 'Small Box',  'category' => 'packaging'],
            ['code' => 'lbox', 'name' => 'Large Box',  'category' => 'packaging'],
        ];

        foreach ($units as $unit) {
            DefaultMeasurementUnit::create($unit);
        }
    }
}
