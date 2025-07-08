<?php

namespace Database\Seeders;

use App\Domain\Financial\Enums\VatRateType;
use App\Domain\Financial\Models\VatRate;
use App\Helpers\Ulid;
use Illuminate\Database\Seeder;

class VatRateSeeder extends Seeder
{
    public function run(): void
    {
        // Polish VAT rates as example
        $vatRates = [
            [
                'name'         => '23%',
                'rate'         => 0.23,
                'type'         => VatRateType::PERCENTAGE,
                'country_code' => 'PL',
                'active'       => true,
                'valid_from'   => '2024-01-01',
                'valid_to'     => null,
            ],
            [
                'name'         => '8%',
                'rate'         => 0.08,
                'type'         => VatRateType::PERCENTAGE,
                'country_code' => 'PL',
                'active'       => true,
                'valid_from'   => '2024-01-01',
                'valid_to'     => null,
            ],
            [
                'name'         => '5%',
                'rate'         => 0.05,
                'type'         => VatRateType::PERCENTAGE,
                'country_code' => 'PL',
                'active'       => true,
                'valid_from'   => '2024-01-01',
                'valid_to'     => null,
            ],
            [
                'name'         => '0%',
                'rate'         => 0.00,
                'type'         => VatRateType::ZERO_PERCENT,
                'country_code' => 'PL',
                'active'       => true,
                'valid_from'   => '2024-01-01',
                'valid_to'     => null,
            ],
            [
                'name'         => 'Exempt',
                'rate'         => 0.00,
                'type'         => VatRateType::EXEMPT,
                'country_code' => 'PL',
                'active'       => true,
                'valid_from'   => '2024-01-01',
                'valid_to'     => null,
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
