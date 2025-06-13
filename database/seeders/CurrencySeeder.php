<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath     = base_path('vendor/mledoze/countries/countries.json');
        $json         = file_get_contents($jsonPath);
        $allCountries = json_decode($json, true);

        $currencies = collect($allCountries)
            ->flatMap(function ($country) {
                if (empty($country['currencies']) || !is_array($country['currencies'])) {
                    return [];
                }

                return collect($country['currencies'])->map(function ($currency, $code) {
                    return [
                        'code'   => $code,
                        'name'   => $currency['name'] ?? $code,
                        'symbol' => $currency['symbol'] ?? $code,
                    ];
                });
            })
            ->unique('code')
            ->values()
            ->toArray()
        ;

        DB::table('currencies')->insert($currencies);
    }
}
