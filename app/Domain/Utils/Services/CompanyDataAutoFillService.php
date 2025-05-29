<?php

namespace App\Domain\Utils\Services;

use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Common\DTOs\CommonCompanyLookupSources;
use App\Services\MfLookup\Services\MfLookupService;
use App\Services\RegonLookup\Services\RegonLookupService;
use App\Services\ViesLookup\Services\ViesLookupService;

class CompanyDataAutoFillService
{
    public function __construct(
        private readonly RegonLookupService $regonLookupService,
        private readonly MfLookupService $mfLookupService,
        private readonly ViesLookupService $viesLookupService,
    ) {
    }

    /**
     * Auto-fill company data from available sources.
     */
    public function autoFill(
        ?string $nip = null,
        ?string $regon = null,
        ?string $country = null,
        bool $force = false
    ): ?CommonCompanyLookupData {
        $regonData = null;
        $mfData    = null;

        if ($nip && config('regon_lookup.user_key')) {
            $regonData = $this->regonLookupService->findByNip($nip, $force);
        } elseif ($regon && config('regon_lookup.user_key')) {
            $regonData = $this->regonLookupService->findByRegon($regon, $force);
        }

        if ($nip) {
            $mfData = $this->mfLookupService->findByNip($nip, $force);
        }

        if ($nip && $country) {
            $viesData = $this->viesLookupService->findByVat($country, $nip, $force);
        }

        if (!$regonData && !$mfData) {
            return null;
        }

        if (!$mfData && $regonData) {
            return $regonData->toCommonLookupData();
        }

        if (!$regonData && $mfData) {
            return $mfData->toCommonLookupData();
        }

        if ($regonData) {
            $regonResult = $regonData->toCommonLookupData();

            // Merge REGON data with MF data, preferring REGON for company info, MF for bank account
            return new CommonCompanyLookupData(
                name: $regonResult->name,
                country: $regonResult->country,
                vatId: $regonResult->vatId,
                regon: $regonResult->regon,
                shortName: $regonResult->shortName,
                phoneNumber: $regonResult->phoneNumber,
                email: $regonResult->email,
                website: $regonResult->website,
                address: $regonResult->address,
                bankAccount: $regonResult->bankAccount,
                sources: new CommonCompanyLookupSources(
                    mf: $mfData ? true : false,
                    regon: $regonData ? true : false,
                    vies: $viesData ? true : false,
                ),
            );
        }

        return null;
    }
}
