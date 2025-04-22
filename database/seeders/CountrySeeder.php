<?php

namespace Database\Seeders;

use App\Domain\Common\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $json      = File::get(database_path('data/countries.json'));
        $countries = json_decode($json, true);

        foreach ($countries as $country) {
            Country::create([
                'name'            => $country['name'],
                'code'            => $country['code'],
                'code3'           => $country['code3'],
                'numeric_code'    => $country['numeric_code'],
                'phone_code'      => $country['phone_code'],
                'capital'         => $country['capital'],
                'currency'        => $country['currency'],
                'currency_code'   => $country['currency_code'],
                'currency_symbol' => $country['currency_symbol'],
                'tld'             => $country['tld'],
                'native'          => $country['native'],
                'region'          => $country['region'],
                'subregion'       => $country['subregion'],
                'emoji'           => $country['emoji'],
                'emojiU'          => $country['emojiU'],
            ]);
        }
    }
}
