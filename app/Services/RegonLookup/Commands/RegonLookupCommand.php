<?php

namespace App\Services\RegonLookup\Commands;

use App\Services\RegonLookup\Services\RegonLookupService;
use Illuminate\Console\Command;

class RegonLookupCommand extends Command
{
    protected $signature = 'regon:lookup {nip} {--force}';

    protected $description = 'Lookup company details by NIP and print result.';

    public function handle(RegonLookupService $service): int
    {
        $nip   = $this->argument('nip');
        $force = $this->option('force');

        $this->info('Looking up company details in REGON for ' . $nip . ($force ? ' (force)' : '') . '...');

        try {
            $company = $service->findByNip($nip, force: $force);

            if (null === $company) {
                $this->warn('No company found for given NIP.');

                return self::FAILURE;
            }

            $this->info('Company Details:');
            $this->line('- Name                : ' . $company->name);
            $this->line('- REGON               : ' . $company->regon);
            $this->line('- NIP                 : ' . ($company->nip ?? 'N/A'));
            $this->line('- Address             : ' . ($company->getAddressAsString() ?? 'N/A'));
            $this->line('- Registration Date   : ' . ($company->registrationDate ?? 'N/A'));
            $this->line('- Business Start Date : ' . ($company->businessStartDate ?? 'N/A'));
            $this->line('- Business End Date   : ' . ($company->businessEndDate ?? 'N/A'));
            $this->line('- Business Suspension Date: ' . ($company->businessSuspensionDate ?? 'N/A'));
            $this->line('- Business Resumption Date: ' . ($company->businessResumptionDate ?? 'N/A'));
            $this->line('- Cache: ' . ($company->cache ?? 'N/A'));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
