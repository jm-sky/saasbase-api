<?php

namespace App\Domain\Utils\Services;

use App\Domain\Utils\DTOs\AllLookupResults;
use App\Domain\Utils\DTOs\CompanyContext;
use App\Services\MfLookup\Services\MfLookupService;
use App\Services\RegonLookup\Services\RegonLookupService;
use App\Services\ViesLookup\Services\ViesLookupService;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class CompanyDataFetcherService
{
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
        $timeout   = 30; // seconds
        $startTime = time();

        while (!$batch->finished() && (time() - $startTime) < $timeout) {
            sleep(1);
            $batch->refresh();
        }

        if (!$batch->finished()) {
            Log::warning('Company data auto-fill batch timed out', ['batch_id' => $batch->id]);

            return null;
        }
    }

    private function mergeResults(Batch $batch): AllLookupResults
    {
        $result = new AllLookupResults();

        // Collect results from successful jobs
        foreach ($batch->jobs as $job) {
            if ($job->hasSucceeded()) {
                $result = $job->getResult();

                if ($job instanceof \App\Domain\Utils\Jobs\RegonLookupJob) {
                    $result->regon = $result;
                } elseif ($job instanceof \App\Domain\Utils\Jobs\MfLookupJob) {
                    $result->mf = $result;
                } elseif ($job instanceof \App\Domain\Utils\Jobs\ViesLookupJob) {
                    $result->vies = $result;
                }
            }
        }

        return $result;
    }
}
