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
     *
     * TODO: Implement batch execution for all sources
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
            try {
                $mfData = $this->mfLookupService->findByNip($nip, $force);
                $mfData = $mfData->toCommonLookupData();
            } catch (\Throwable $e) {
                $mfData = null;
            }
        }

        if ($nip && $country) {
            try {
                $viesData = $this->viesLookupService->findByVat($country, $nip, $force);
                $viesData = $viesData->toCommonLookupData();
            } catch (\Throwable $e) {
                $viesData = null;
            }
        }

        if (!$regonData && !$mfData && !$viesData) {
            return null;
        }

        if ($regonData) {
            $regonResult = $regonData->toCommonLookupData();

            // Merge REGON data with MF data, preferring REGON for company info, MF for bank account
            return new CommonCompanyLookupData(
                name: $regonResult?->name ?? $mfData?->name ?? $viesData?->name,
                country: $regonResult?->country ?? $mfData?->country ?? $viesData?->country,
                vatId: $regonResult?->vatId ?? $mfData?->vatId ?? $viesData?->vatId,
                regon: $regonResult?->regon ?? $mfData?->regon ?? $viesData?->regon,
                shortName: $regonResult?->shortName ?? $mfData?->shortName ?? $viesData?->shortName,
                phoneNumber: $regonResult?->phoneNumber ?? $mfData?->phoneNumber ?? $viesData?->phoneNumber,
                email: $regonResult?->email ?? $mfData?->email ?? $viesData?->email,
                website: $regonResult?->website ?? $mfData?->website ?? $viesData?->website,
                address: $regonResult?->address ?? $mfData?->address ?? $viesData?->address,
                bankAccount: $regonResult?->bankAccount ?? $mfData?->bankAccount ?? $viesData?->bankAccount,
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
