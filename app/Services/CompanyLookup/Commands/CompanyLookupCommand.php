<?php

namespace App\Services\CompanyLookup\Commands;

use App\Services\CompanyLookup\Services\CompanyLookupService;
use Illuminate\Console\Command;

class CompanyLookupCommand extends Command
{
    protected $signature = 'company:lookup {nip}';

    protected $description = 'Lookup company details by NIP and print result.';

    public function handle(CompanyLookupService $service): int
    {
        $nip = $this->argument('nip');

        try {
            $company = $service->findByNip($nip);

            if (null === $company) {
                $this->warn('No company found for given NIP.');

                return self::FAILURE;
            }

            $this->info('Company Details:');
            $this->line('Name: ' . $company->name);
            $this->line('NIP: ' . $company->nip);
            $this->line('Address: ' . $company->address);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
