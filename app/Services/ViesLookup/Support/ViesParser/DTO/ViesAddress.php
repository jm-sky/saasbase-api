<?php

namespace App\Services\ViesLookup\Support\ViesParser\DTO;

use Illuminate\Support\Str;

class ViesAddress
{
    public function __construct(
        public string $address,
        public string $street,
        public ?string $zip,
        public string $city,
        public ?string $countryCode,
        public ?string $building = null,
        public ?string $flat = null,
    ) {
        $this->parseStreet($street);
    }

    protected function parseStreet(string $street): void
    {
        $this->street = trim($street);

        $parts = explode(' ', $this->street);

        if (1 === count($parts)) {
            return;
        }

        $last = array_pop($parts);

        if (!Str::match('/^\d+$/', $last)) {
            return;
        }

        $this->building = $last;

        $this->street = implode(' ', $parts);
    }
}
