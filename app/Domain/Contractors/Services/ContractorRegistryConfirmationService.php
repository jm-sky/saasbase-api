<?php

namespace App\Domain\Contractors\Services;

use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Services\RegistryConfirmation\MfContractorRegistryConfirmationService;
use App\Domain\Contractors\Services\RegistryConfirmation\RegonContractorRegistryConfirmationService;
use App\Domain\Contractors\Services\RegistryConfirmation\ViesContractorRegistryConfirmationService;
use App\Domain\Utils\DTOs\CompanyContext;
use App\Domain\Utils\Enums\RegistryConfirmationStatus;
use App\Domain\Utils\Models\RegistryConfirmation;
use App\Domain\Utils\Services\CompanyDataFetcherService;
use Illuminate\Support\Facades\Log;

/**
 * Main service for confirming contractor data against multiple registries.
 *
 * This service orchestrates the confirmation process by:
 * 1. Fetching data from all available registries (REGON, VIES, MF)
 * 2. Using registry-specific services to create confirmations
 * 3. Handling errors and logging appropriately
 */
class ContractorRegistryConfirmationService
{
    public function __construct(
        private readonly CompanyDataFetcherService $dataFetcherService,
        private readonly RegonContractorRegistryConfirmationService $regonService,
        private readonly ViesContractorRegistryConfirmationService $viesService,
        private readonly MfContractorRegistryConfirmationService $mfService,
    ) {
    }

    /**
     * Confirm contractor data against all available registries.
     *
     * @return RegistryConfirmation[]
     */
    public function confirm(Contractor $contractor): array
    {
        $allConfirmations = [];

        try {
            // Fetch data from all registries
            $companyContext = new CompanyContext(
                $contractor->vat_id,
                $contractor->regon,
                $contractor->country,
                force: false,
            );

            $allLookupResults = $this->dataFetcherService->fetch($companyContext);

            if (!$allLookupResults) {
                Log::warning('No registry data found for contractor', [
                    'contractor_id' => $contractor->id,
                    'vat_id'        => $contractor->vat_id,
                    'regon'         => $contractor->regon,
                ]);

                return [];
            }

            // Process REGON confirmations
            if ($allLookupResults->regon) {
                try {
                    $regonConfirmations = $this->regonService->confirmContractorData($contractor, $allLookupResults->regon);
                    $allConfirmations   = array_merge($allConfirmations, $regonConfirmations);
                } catch (\Exception $e) {
                    Log::error('Error processing REGON confirmations', [
                        'contractor_id' => $contractor->id,
                        'error'         => $e->getMessage(),
                    ]);
                }
            }

            // Process VIES confirmations
            if ($allLookupResults->vies) {
                try {
                    $viesConfirmations = $this->viesService->confirmContractorData($contractor, $allLookupResults->vies);
                    $allConfirmations  = array_merge($allConfirmations, $viesConfirmations);
                } catch (\Exception $e) {
                    Log::error('Error processing VIES confirmations', [
                        'contractor_id' => $contractor->id,
                        'error'         => $e->getMessage(),
                    ]);
                }
            }

            // Process MF confirmations
            if ($allLookupResults->mf) {
                try {
                    $mfConfirmations  = $this->mfService->confirmContractorData($contractor, $allLookupResults->mf);
                    $allConfirmations = array_merge($allConfirmations, $mfConfirmations);
                } catch (\Exception $e) {
                    Log::error('Error processing MF confirmations', [
                        'contractor_id' => $contractor->id,
                        'error'         => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Registry confirmations completed', [
                'contractor_id'         => $contractor->id,
                'confirmations_created' => count($allConfirmations),
                'registries_used'       => [
                    'regon' => $allLookupResults->regon ? true : false,
                    'vies'  => $allLookupResults->vies ? true : false,
                    'mf'    => $allLookupResults->mf ? true : false,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error during registry confirmation process', [
                'contractor_id' => $contractor->id,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);
        }

        return $allConfirmations;
    }

    /**
     * Get all confirmations for a contractor.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConfirmations(Contractor $contractor)
    {
        return $contractor->registryConfirmations()->orderBy('checked_at', 'desc')->get();
    }

    /**
     * Get the most recent confirmation for a specific registry type.
     */
    public function getLatestConfirmation(Contractor $contractor, string $registryType): ?RegistryConfirmation
    {
        // @phpstan-ignore-next-line
        return $contractor->registryConfirmations()
            ->where('type', $registryType)
            ->orderBy('checked_at', 'desc')
            ->first()
        ;
    }

    /**
     * Check if contractor has successful confirmations.
     */
    public function hasSuccessfulConfirmations(Contractor $contractor): bool
    {
        return $contractor->registryConfirmations()
            ->where('status', RegistryConfirmationStatus::Success)
            ->exists()
        ;
    }
}
