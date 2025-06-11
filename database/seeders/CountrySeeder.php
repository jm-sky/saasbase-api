<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run()
    {
        $jsonPath     = base_path('vendor/mledoze/countries/countries.json');
        $json         = file_get_contents($jsonPath);
        $allCountries = json_decode($json, true);

        $formattedCountries = collect($allCountries)->map(function ($country) {
            // Get currency code and symbol (first currency if multiple)
            $currencyCode   = null;
            $currencySymbol = null;

            if (!empty($country['currencies']) && is_array($country['currencies'])) {
                $firstCurrency  = reset($country['currencies']);
                $currencyCode   = key($country['currencies']);
                $currencySymbol = $firstCurrency['symbol'] ?? null;
            }

            // Get phone code (idd root + first suffix)
            $phoneCode = null;

            if (!empty($country['idd']['root']) && !empty($country['idd']['suffixes'][0])) {
                $phoneCode = $country['idd']['root'] . $country['idd']['suffixes'][0];
            }

            return [
                'name'            => $country['name']['common'] ?? null,
                'code'            => $country['cca2'] ?? null,
                'code3'           => $country['cca3'] ?? null,
                'numeric_code'    => $country['ccn3'] ?? null,
                'currency_code'   => $currencyCode,
                'currency_symbol' => $currencySymbol,
                'phone_code'      => $phoneCode,
                'tld'             => $country['tld'][0] ?? null,
                'capital'         => $country['capital'][0] ?? null,
                'region'          => $country['region'] ?? null,
                'subregion'       => $country['subregion'] ?? null,
                'emoji'           => $country['flag'] ?? null,
                'native'          => $this->getNativeName($country),
            ];
        })->toArray();

        DB::table('countries')->insert($formattedCountries);
    }

    private function getNativeName(array $country): ?string
    {
        if (!empty($country['name']['native']) && is_array($country['name']['native'])) {
            $firstNative = reset($country['name']['native']);

            return $firstNative['common'] ?? null;
        }

        return null;
    }
}
