<?php

namespace Tests\Traits;

use App\Domain\Common\Models\Country;

trait WithCountries
{
    public const DEFAULT_COUNTRY = 'PL';

    public const SECONDARY_COUNTRY = 'US';

    public function seedCountries(): void
    {
        Country::create([
            'code'  => self::DEFAULT_COUNTRY,
            'code3' => 'POL',
            'name'  => 'Poland',
        ]);

        Country::create([
            'code'  => self::SECONDARY_COUNTRY,
            'code3' => 'USA',
            'name'  => 'United States',
        ]);
    }
}
