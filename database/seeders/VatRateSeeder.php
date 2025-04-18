<?php

namespace Database\Seeders;

use App\Domain\Common\Models\VatRate;
use Illuminate\Database\Seeder;

class VatRateSeeder extends Seeder
{
    public function run(): void
    {
        // Polish VAT rates as example
        $vatRates = [
            [
                'name' => 'Standard rate',
                'rate' => 23.00,
            ],
            [
                'name' => 'Reduced rate',
                'rate' => 8.00,
            ],
            [
                'name' => 'Super-reduced rate',
                'rate' => 5.00,
            ],
            [
                'name' => 'Zero rate',
                'rate' => 0.00,
            ],
            [
                'name' => 'Exempt',
                'rate' => 0.00,
            ],
        ];

        foreach ($vatRates as $vatRate) {
            VatRate::create($vatRate);
        }
    }
}
