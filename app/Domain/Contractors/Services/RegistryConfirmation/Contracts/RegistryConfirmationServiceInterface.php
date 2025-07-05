<?php

namespace App\Domain\Contractors\Services\RegistryConfirmation\Contracts;

use App\Domain\Contractors\Models\Contractor;
use App\Domain\Utils\Models\RegistryConfirmation;

interface RegistryConfirmationServiceInterface
{
    /**
     * Confirm contractor data against registry data.
     *
     * @return RegistryConfirmation[]
     */
    public function confirmContractorData(Contractor $contractor, $registryData): array;

    /**
     * Get the registry type this service handles.
     */
    public function getRegistryType(): string;
}
