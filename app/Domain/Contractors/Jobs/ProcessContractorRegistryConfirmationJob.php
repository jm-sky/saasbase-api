<?php

namespace App\Domain\Contractors\Jobs;

use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Services\ContractorRegistryConfirmationService;
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

    public int $tries = 3;

    public int $maxExceptions = 3;

    public int $timeout = 120; // 2 minutes timeout for external API calls

    public function __construct(
        protected Contractor $contractor
    ) {
        // Set queue name for better organization
        $this->onQueue('registry-confirmations');
    }

    public function handle(ContractorRegistryConfirmationService $confirmationService): void
    {
        try {
            Log::info('Starting registry confirmation process', [
                'contractor_id'   => $this->contractor->id,
                'contractor_name' => $this->contractor->name,
            ]);

            $confirmations = $confirmationService->confirm($this->contractor);

            Log::info('Registry confirmation process completed', [
                'contractor_id'       => $this->contractor->id,
                'confirmations_count' => count($confirmations),
                'confirmation_ids'    => collect($confirmations)->pluck('id')->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Registry confirmation process failed', [
                'contractor_id'   => $this->contractor->id,
                'contractor_name' => $this->contractor->name,
                'error'           => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Registry confirmation job failed permanently', [
            'contractor_id'   => $this->contractor->id,
            'contractor_name' => $this->contractor->name,
            'error'           => $exception->getMessage(),
            'attempts'        => $this->attempts(),
        ]);
    }
}
