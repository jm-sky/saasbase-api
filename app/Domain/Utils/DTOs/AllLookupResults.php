<?php

namespace App\Domain\Utils\DTOs;

use App\Services\MfLookup\DTOs\MfLookupResultDTO;
use App\Services\RegonLookup\DTOs\RegonReportUnified;
use App\Services\ViesLookup\DTOs\ViesLookupResultDTO;

class AllLookupResults
{
    public function __construct(
        public ?RegonReportUnified $regon = null,
        public ?MfLookupResultDTO $mf = null,
        public ?ViesLookupResultDTO $vies = null,
    ) {
    }
}
