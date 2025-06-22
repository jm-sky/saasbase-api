<?php

namespace App\Domain\Utils\Jobs;

use App\Domain\Utils\DTOs\CompanyContext;
use App\Services\ViesLookup\Services\ViesLookupService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ViesLookupJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public CompanyContext $context,
    ) {
    }

    /**
     * Executes the job.
     *
     * Performs the VIES lookup and stores the successful result in the cache,
     * using a key that is unique to this job's batch instance.
     */
    public function handle(ViesLookupService $viesLookupService): void
    {
        if ($this->batch()->cancelled() || !$this->context->nip || !$this->context->country) {
            return;
        }

        $result = $viesLookupService->findByVat($this->context->country, $this->context->nip);

        if ($result) {
            Cache::put($this->getCacheKey(), $result, now()->addMinutes(10));
        }
    }

    /**
     * Generates a unique cache key for the job's result.
     * The key is tied to the batch ID to ensure it's unique per execution.
     */
    public function getCacheKey(): string
    {
        return "batch:{$this->batch()->id}:vies";
    }
}
