You're absolutely right! Running these external API calls sequentially is inefficient. Let me search for how Laravel handles parallel/async operations and see what patterns are already used in the codebase.

Search files...
Now let me search for more specific patterns around concurrent/parallel execution in Laravel:

Search files...
Let me search for more specific patterns around concurrent execution in Laravel:

Ran tool
Now let me check if there are any existing patterns for concurrent HTTP requests in the codebase:

Search files...
Perfect! Now I can see that the codebase uses Saloon for HTTP requests and has a well-established queue system with Laravel Horizon. Let me check if there are any existing patterns for concurrent execution in Laravel:

Search files...
Based on my analysis of the codebase, I can see that you're absolutely right! The current implementation runs the three external API calls sequentially, which is inefficient. The codebase already has a robust queue system with Laravel Horizon and uses Saloon for HTTP requests. Here are several approaches to make these calls concurrent:

## Option 1: Using Laravel's Job Batching (Recommended)

This is the most Laravel-native approach and fits well with your existing patterns:

```php:app/Domain/Utils/Services/CompanyDataAutoFillService.php
<?php

namespace App\Domain\Utils\Services;

use App\Domain\Common\DTOs\BankAccountDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Common\DTOs\CommonCompanyLookupSources;
use App\Services\IbanInfo\IbanInfoService;
use App\Services\MfLookup\Services\MfLookupService;
use App\Services\RegonLookup\Services\RegonLookupService;
use App\Services\ViesLookup\Services\ViesLookupService;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class CompanyDataAutoFillService
{
    public function __construct(
        private readonly RegonLookupService $regonLookupService,
        private readonly MfLookupService $mfLookupService,
        private readonly ViesLookupService $viesLookupService,
        private readonly IbanInfoService $ibanInfoService,
    ) {
    }

    /**
     * Auto-fill company data from available sources using concurrent jobs.
     */
    public function autoFill(
        ?string $nip = null,
        ?string $regon = null,
        ?string $country = null,
        bool $force = false
    ): ?CommonCompanyLookupData {
        $jobs = [];
        $context = [
            'nip' => $nip,
            'regon' => $regon,
            'country' => $country,
            'force' => $force,
        ];

        // Prepare jobs for each service
        if ($nip && config('services.regon.user_key')) {
            $jobs[] = new \App\Domain\Utils\Jobs\RegonLookupJob($nip, $context);
        } elseif ($regon && config('services.regon.user_key')) {
            $jobs[] = new \App\Domain\Utils\Jobs\RegonLookupJob($regon, $context, true);
        }

        if ($nip) {
            $jobs[] = new \App\Domain\Utils\Jobs\MfLookupJob($nip, $context);
        }

        if ($nip && $country) {
            $jobs[] = new \App\Domain\Utils\Jobs\ViesLookupJob($nip, $country, $context);
        }

        if (empty($jobs)) {
            return null;
        }

        // Execute jobs concurrently and wait for results
        $batch = Bus::batch($jobs)
            ->allowFailures()
            ->dispatch();

        // Wait for completion (with timeout)
        $timeout = 30; // seconds
        $startTime = time();
        
        while (!$batch->finished() && (time() - $startTime) < $timeout) {
            sleep(1);
            $batch->refresh();
        }

        if (!$batch->finished()) {
            Log::warning('Company data auto-fill batch timed out', ['batch_id' => $batch->id]);
            return null;
        }

        return $this->mergeResults($batch, $context);
    }

    private function mergeResults(Batch $batch, array $context): ?CommonCompanyLookupData
    {
        $regonData = null;
        $mfData = null;
        $viesData = null;

        // Collect results from successful jobs
        foreach ($batch->jobs as $job) {
            if ($job->hasSucceeded()) {
                $result = $job->getResult();
                
                if ($job instanceof \App\Domain\Utils\Jobs\RegonLookupJob) {
                    $regonData = $result;
                } elseif ($job instanceof \App\Domain\Utils\Jobs\MfLookupJob) {
                    $mfData = $result;
                } elseif ($job instanceof \App\Domain\Utils\Jobs\ViesLookupJob) {
                    $viesData = $result;
                }
            }
        }

        if (!$regonData && !$mfData && !$viesData) {
            return null;
        }

        // Merge results (same logic as before)
        if ($regonData) {
            $regonResult = $regonData->toCommonLookupData();

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

    // ... existing fillBankAccountDetails method ...
}
```

## Option 2: Using Saloon's Concurrent Requests

Since you're already using Saloon, you can leverage its concurrent request capabilities:

```php:app/Domain/Utils/Services/CompanyDataAutoFillService.php
<?php

namespace App\Domain\Utils\Services;

use App\Domain\Common\DTOs\BankAccountDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Common\DTOs\CommonCompanyLookupSources;
use App\Services\IbanInfo\IbanInfoService;
use App\Services\MfLookup\Services\MfLookupService;
use App\Services\RegonLookup\Services\RegonLookupService;
use App\Services\ViesLookup\Services\ViesLookupService;
use Saloon\Http\Connector;
use Saloon\Http\Request;

class CompanyDataAutoFillService
{
    public function __construct(
        private readonly RegonLookupService $regonLookupService,
        private readonly MfLookupService $mfLookupService,
        private readonly ViesLookupService $viesLookupService,
        private readonly IbanInfoService $ibanInfoService,
    ) {
    }

    /**
     * Auto-fill company data from available sources using concurrent requests.
     */
    public function autoFill(
        ?string $nip = null,
        ?string $regon = null,
        ?string $country = null,
        bool $force = false
    ): ?CommonCompanyLookupData {
        $requests = [];
        $context = [
            'nip' => $nip,
            'regon' => $regon,
            'country' => $country,
            'force' => $force,
        ];

        // Prepare concurrent requests
        if ($nip && config('services.regon.user_key')) {
            $requests['regon'] = $this->prepareRegonRequest($nip, $context);
        } elseif ($regon && config('services.regon.user_key')) {
            $requests['regon'] = $this->prepareRegonRequest($regon, $context, true);
        }

        if ($nip) {
            $requests['mf'] = $this->prepareMfRequest($nip, $context);
        }

        if ($nip && $country) {
            $requests['vies'] = $this->prepareViesRequest($nip, $country, $context);
        }

        if (empty($requests)) {
            return null;
        }

        // Execute requests concurrently
        $results = $this->executeConcurrentRequests($requests);

        return $this->mergeResults($results, $context);
    }

    private function executeConcurrentRequests(array $requests): array
    {
        $results = [];
        
        // Use Saloon's concurrent request capability
        $connector = new Connector();
        
        // Execute all requests concurrently
        $responses = $connector->sendMultiple($requests);
        
        foreach ($responses as $key => $response) {
            try {
                if ($response->successful()) {
                    $results[$key] = $response->dto();
                }
            } catch (\Throwable $e) {
                // Log error but continue with other results
                \Log::warning("Failed to get {$key} data", ['error' => $e->getMessage()]);
            }
        }

        return $results;
    }

    private function mergeResults(array $results, array $context): ?CommonCompanyLookupData
    {
        $regonData = $results['regon'] ?? null;
        $mfData = $results['mf'] ?? null;
        $viesData = $results['vies'] ?? null;

        if (!$regonData && !$mfData && !$viesData) {
            return null;
        }

        // Same merging logic as before...
        if ($regonData) {
            $regonResult = $regonData->toCommonLookupData();

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

    // ... existing fillBankAccountDetails method ...
}
```

## Option 3: Simple Promise-like Approach with Guzzle

For a simpler approach without job complexity:

```php:app/Domain/Utils/Services/CompanyDataAutoFillService.php
<?php

namespace App\Domain\Utils\Services;

use App\Domain\Common\DTOs\BankAccountDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Common\DTOs\CommonCompanyLookupSources;
use App\Services\IbanInfo\IbanInfoService;
use App\Services\MfLookup\Services\MfLookupService;
use App\Services\RegonLookup\Services\RegonLookupService;
use App\Services\ViesLookup\Services\ViesLookupService;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class CompanyDataAutoFillService
{
    public function __construct(
        private readonly RegonLookupService $regonLookupService,
        private readonly MfLookupService $mfLookupService,
        private readonly ViesLookupService $viesLookupService,
        private readonly IbanInfoService $ibanInfoService,
    ) {
    }

    /**
     * Auto-fill company data from available sources using concurrent requests.
     */
    public function autoFill(
        ?string $nip = null,
        ?string $regon = null,
        ?string $country = null,
        bool $force = false
    ): ?CommonCompanyLookupData {
        $promises = [];
        $context = [
            'nip' => $nip,
            'regon' => $regon,
            'country' => $country,
            'force' => $force,
        ];

        // Create promises for each service
        if ($nip && config('services.regon.user_key')) {
            $promises['regon'] = $this->createRegonPromise($nip, $context);
        } elseif ($regon && config('services.regon.user_key')) {
            $promises['regon'] = $this->createRegonPromise($regon, $context, true);
        }

        if ($nip) {
            $promises['mf'] = $this->createMfPromise($nip, $context);
        }

        if ($nip && $country) {
            $promises['vies'] = $this->createViesPromise($nip, $country, $context);
        }

        if (empty($promises)) {
            return null;
        }

        // Wait for all promises to resolve
        $results = Promise\Utils::settle($promises)->wait();

        return $this->mergeResults($results, $context);
    }

    private function createRegonPromise(string $identifier, array $context, bool $isRegon = false): \GuzzleHttp\Promise\PromiseInterface
    {
        return \GuzzleHttp\Promise\Create::promiseFor(
            $isRegon 
                ? $this->regonLookupService->findByRegon($identifier, $context['force'])
                : $this->regonLookupService->findByNip($identifier, $context['force'])
        );
    }

    private function createMfPromise(string $nip, array $context): \GuzzleHttp\Promise\PromiseInterface
    {
        return \GuzzleHttp\Promise\Create::promiseFor(
            $this->mfLookupService->findByNip($nip, $context['force'])
        );
    }

    private function createViesPromise(string $nip, string $country, array $context): \GuzzleHttp\Promise\PromiseInterface
    {
        return \GuzzleHttp\Promise\Create::promiseFor(
            $this->viesLookupService->findByVat($country, $nip, $context['force'])
        );
    }

    private function mergeResults(array $results, array $context): ?CommonCompanyLookupData
    {
        $regonData = null;
        $mfData = null;
        $viesData = null;

        foreach ($results as $key => $result) {
            if ($result['state'] === 'fulfilled') {
                $data = $result['value'];
                
                if ($key === 'regon') {
                    $regonData = $data;
                } elseif ($key === 'mf') {
                    $mfData = $data->toCommonLookupData();
                } elseif ($key === 'vies') {
                    $viesData = $data->toCommonLookupData();
                }
            }
        }

        // Same merging logic as before...
        if (!$regonData && !$mfData && !$viesData) {
            return null;
        }

        if ($regonData) {
            $regonResult = $regonData->toCommonLookupData();

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

    // ... existing fillBankAccountDetails method ...
}
```

## Recommendation

I'd recommend **Option 1 (Job Batching)** because:

1. **Fits your existing patterns** - You already use Laravel jobs extensively
2. **Better error handling** - Failed jobs are logged and can be retried
3. **Scalability** - Jobs can be distributed across multiple workers
4. **Monitoring** - Laravel Horizon provides excellent monitoring
5. **Rate limiting** - Each service can have its own rate limiting
6. **Resilience** - If one service is slow/failing, others continue

The job batching approach would require creating individual job classes for each service, but it provides the most robust and scalable solution that aligns with your existing architecture.

Would you like me to implement the job classes for Option 1, or would you prefer one of the other approaches?