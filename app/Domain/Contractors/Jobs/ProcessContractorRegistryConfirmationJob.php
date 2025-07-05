<?php

namespace App\Domain\Contractors\Jobs;

use App\Domain\Contractors\Services\RegistryConfirmation\MfContractorRegistryConfirmationService;
use App\Domain\Contractors\Services\RegistryConfirmation\RegonContractorRegistryConfirmationService;
use App\Domain\Contractors\Services\RegistryConfirmation\ViesContractorRegistryConfirmationService;
use App\Domain\Utils\DTOs\CompanyContext;
use App\Domain\Utils\Enums\RegistryConfirmationStatus;
use App\Domain\Utils\Enums\RegistryConfirmationType;
use App\Domain\Utils\Models\RegistryConfirmation;
use App\Domain\Utils\Services\CompanyDataFetcherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessContractorRegistryConfirmationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 120;

    public int $tries = 3;

    public function __construct(
        public RegistryConfirmation $confirmation,
    ) {
        $this->onQueue('registry-confirmations');
    }

    public function handle(
        CompanyDataFetcherService $dataFetcherService,
        RegonContractorRegistryConfirmationService $regonService,
        ViesContractorRegistryConfirmationService $viesService,
        MfContractorRegistryConfirmationService $mfService,
    ): void {
        try {
            Log::info('Processing registry confirmation', [
                'confirmation_id' => $this->confirmation->id,
                'type'            => $this->confirmation->type,
                'job_id'          => $this->job?->getJobId(),
            ]);

            $result = $this->processConfirmation(
                $dataFetcherService,
                $regonService,
                $viesService,
                $mfService
            );

            $this->confirmation->update([
                'result'     => $result,
                'status'     => RegistryConfirmationStatus::Success,
                'checked_at' => now(),
            ]);

            Log::info('Registry confirmation completed successfully', [
                'confirmation_id' => $this->confirmation->id,
                'type'            => $this->confirmation->type,
                'job_id'          => $this->job?->getJobId(),
            ]);
        } catch (\Exception $e) {
            $this->confirmation->update([
                'result'     => ['error' => $e->getMessage()],
                'status'     => RegistryConfirmationStatus::Failed,
                'checked_at' => now(),
            ]);

            Log::error('Registry confirmation failed', [
                'confirmation_id' => $this->confirmation->id,
                'type'            => $this->confirmation->type,
                'error'           => $e->getMessage(),
                'job_id'          => $this->job?->getJobId(),
            ]);

            throw $e;
        }
    }

    private function processConfirmation(
        CompanyDataFetcherService $dataFetcherService,
        RegonContractorRegistryConfirmationService $regonService,
        ViesContractorRegistryConfirmationService $viesService,
        MfContractorRegistryConfirmationService $mfService,
    ): array {
        $payload = $this->confirmation->payload;
        $context = new CompanyContext(
            nip: $payload['nip'] ?? null,
            regon: $payload['regon'] ?? null,
            country: $payload['country'] ?? null,
            force: $payload['force'] ?? false,
        );

        // Fetch data from external services
        $lookupResults = $dataFetcherService->fetch($context);

        if (!$lookupResults) {
            throw new \Exception('No data available from external registries');
        }

        $contractor = $this->confirmation->load('confirmable')->confirmable;
        $type       = RegistryConfirmationType::from($this->confirmation->type);

        // Process based on confirmation type
        return match ($type) {
            RegistryConfirmationType::Regon => $lookupResults->regon
                ? $regonService->confirmContractorData($contractor, $lookupResults->regon)
                : [],
            RegistryConfirmationType::Vies => $lookupResults->vies
                ? $viesService->confirmContractorData($contractor, $lookupResults->vies)
                : [],
            RegistryConfirmationType::Mf => $lookupResults->mf
                ? $mfService->confirmContractorData($contractor, $lookupResults->mf)
                : [],
            default => throw new \Exception("Unsupported confirmation type: {$type->value}"),
        };
    }

    public function failed(\Exception $exception): void
    {
        $this->confirmation->update([
            'result'     => ['error' => $exception->getMessage()],
            'status'     => RegistryConfirmationStatus::Failed,
            'checked_at' => now(),
        ]);

        Log::error('Registry confirmation job permanently failed', [
            'confirmation_id' => $this->confirmation->id,
            'type'            => $this->confirmation->type,
            'error'           => $exception->getMessage(),
            'job_id'          => $this->job?->getJobId(),
        ]);
    }
}
