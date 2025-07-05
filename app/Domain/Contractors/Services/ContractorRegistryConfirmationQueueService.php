<?php

namespace App\Domain\Contractors\Services;

use App\Domain\Contractors\Jobs\ProcessContractorRegistryConfirmationJob;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Utils\DTOs\CompanyContext;
use App\Domain\Utils\Enums\RegistryConfirmationStatus;
use App\Domain\Utils\Enums\RegistryConfirmationType;
use App\Domain\Utils\Models\RegistryConfirmation;
use Illuminate\Support\Facades\Log;

class ContractorRegistryConfirmationQueueService
{
    /**
     * Create pending registry confirmations and dispatch jobs to process them.
     */
    public function queueConfirmations(Contractor $contractor): array
    {
        $confirmations = [];

        // Create CompanyContext from contractor data
        $context = new CompanyContext(
            nip: $contractor->vat_id,
            regon: $contractor->regon,
            country: $contractor->country,
            force: false
        );

        // Determine which confirmations to create based on available data
        $confirmationTypes = $this->getConfirmationTypes($contractor);

        foreach ($confirmationTypes as $type) {
            $confirmation    = $this->createPendingConfirmation($contractor, $type, $context);
            $confirmations[] = $confirmation;

            // Dispatch job to process this confirmation
            ProcessContractorRegistryConfirmationJob::dispatch($confirmation);

            Log::info('Queued registry confirmation job', [
                'contractor_id'   => $contractor->id,
                'confirmation_id' => $confirmation->id,
                'type'            => $type->value,
            ]);
        }

        return $confirmations;
    }

    /**
     * Create a pending registry confirmation record.
     */
    private function createPendingConfirmation(
        Contractor $contractor,
        RegistryConfirmationType $type,
        CompanyContext $context
    ): RegistryConfirmation {
        return RegistryConfirmation::create([
            'confirmable_id'   => $contractor->id,
            'confirmable_type' => get_class($contractor),
            'type'             => $type->value,
            'payload'          => [
                'nip'     => $context->nip,
                'regon'   => $context->regon,
                'country' => $context->country,
                'force'   => $context->force,
            ],
            'result'     => null,
            'status'     => RegistryConfirmationStatus::Pending,
            'checked_at' => null,
        ]);
    }

    /**
     * Determine which confirmation types to create based on contractor data.
     */
    private function getConfirmationTypes(Contractor $contractor): array
    {
        $types = [];

        // REGON confirmation if we have NIP or REGON and service is configured
        if (($contractor->vat_id || $contractor->regon) && config('services.regon.user_key')) {
            $types[] = RegistryConfirmationType::Regon;
        }

        // MF confirmation if we have NIP
        if ($contractor->vat_id) {
            $types[] = RegistryConfirmationType::Mf;
        }

        // VIES confirmation if we have NIP and country
        if ($contractor->vat_id && $contractor->country) {
            $types[] = RegistryConfirmationType::Vies;
        }

        return $types;
    }
}
