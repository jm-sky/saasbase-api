<?php

namespace Database\Seeders;

use App\Domain\Common\Models\VatRate;
use App\Helpers\Ulid;
use Illuminate\Database\Seeder;

class VatRateSeeder extends Seeder
{
    public function run(): void
    {
        // Polish VAT rates as example
        $vatRates = [
            [
                'name' => '23%',
                'rate' => 0.23,
            ],
            [
                'name' => '8%',
                'rate' => 0.08,
            ],
            [
                'name' => '5%',
                'rate' => 0.05,
            ],
            [
                'name' => '0%',
                'rate' => 0.00,
            ],
            [
                'name' => 'Exempt',
                'rate' => 0.00,
            ],
        ];

        foreach ($vatRates as $vatRate) {
            VatRate::create([
                'id' => Ulid::deterministic(['vat-rate', $vatRate['name']]),
                ...$vatRate,
            ]);
        }
    }
}
