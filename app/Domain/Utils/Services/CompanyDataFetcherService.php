<?php

namespace App\Domain\Utils\Services;

use App\Domain\Utils\DTOs\AllLookupResults;
use App\Domain\Utils\DTOs\CompanyContext;
use App\Services\MfLookup\Services\MfLookupService;
use App\Services\RegonLookup\Services\RegonLookupService;
use App\Services\ViesLookup\Services\ViesLookupService;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CompanyDataFetcherService
{
    private const BATCH_TIMEOUT = 10;

    public function __construct(
        private readonly RegonLookupService $regonLookupService,
        private readonly MfLookupService $mfLookupService,
        private readonly ViesLookupService $viesLookupService,
    ) {
    }

    public function fetch(CompanyContext $context): ?AllLookupResults
    {
        $jobs = [];

        // Prepare jobs for each service
        if (($context->nip || $context->regon) && config('services.regon.user_key')) {
            $jobs[] = new \App\Domain\Utils\Jobs\RegonLookupJob($context);
        }

        if ($context->nip) {
            $jobs[] = new \App\Domain\Utils\Jobs\MfLookupJob($context);
        }

        if ($context->nip && $context->country) {
            $jobs[] = new \App\Domain\Utils\Jobs\ViesLookupJob($context);
        }

        if (empty($jobs)) {
            return null;
        }

        // Execute jobs concurrently and wait for results
        $batch = Bus::batch($jobs)
            ->allowFailures()
            ->dispatch()
        ;

        $this->pollJobs($batch);

        return $this->mergeResults($batch);
    }

    private function pollJobs($batch)
    {
        // Wait for completion (with timeout)
        $timeout   = self::BATCH_TIMEOUT; // seconds
        $startTime = time();
        $batchId   = $batch->id; // Store batch ID for logging

        while (!$batch->finished() && (time() - $startTime) < $timeout) {
            sleep(1);
            // Refresh the batch by fetching it again from the database
            $batch = Bus::findBatch($batchId);

            if (!$batch) {
                Log::warning('Batch not found during polling', ['batch_id' => $batchId]);
                break;
            }
        }

        if ($batch && !$batch->finished()) {
            Log::warning('Company data auto-fill batch timed out', ['batch_id' => $batchId]);

            return null;
        }
    }

    /**
     * Merges results from the completed batch jobs.
     *
     * Each job in the batch is responsible for storing its own result in the cache,
     * using a unique key composed of the batch ID and the job type. This method
     * retrieves those results from the cache and consolidates them into a single DTO.
     *
     * @param Batch $batch the completed job batch
     *
     * @return AllLookupResults a DTO containing the merged results from all lookups
     */
    private function mergeResults(Batch $batch): AllLookupResults
    {
        $result = new AllLookupResults();

        $regonData = Cache::get("batch:{$batch->id}:regon");

        if ($regonData) {
            $result->regon = $regonData;
            Cache::forget("batch:{$batch->id}:regon");
        }

        $mfData = Cache::get("batch:{$batch->id}:mf");

        if ($mfData) {
            $result->mf = $mfData;
            Cache::forget("batch:{$batch->id}:mf");
        }

        $viesData = Cache::get("batch:{$batch->id}:vies");

        if ($viesData) {
            $result->vies = $viesData;
            Cache::forget("batch:{$batch->id}:vies");
        }

        return $result;
    }
}
