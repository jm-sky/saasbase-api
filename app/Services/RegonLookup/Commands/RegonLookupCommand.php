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

        try {
            $company = $service->findByNip($nip, force: $force);

            if (null === $company) {
                $this->warn('No company found for given NIP.');

                return self::FAILURE;
            }

            $this->info('Company Details:');
            $this->line('Name: ' . $company->name);
            $this->line('REGON: ' . $company->regon);
            $this->line('NIP: ' . ($company->nip ?? 'N/A'));
            $this->line('KRS: ' . ($company->krs ?? 'N/A'));
            $this->line('Residence Address: ' . ($company->residenceAddress ?? 'N/A'));
            $this->line('Working Address: ' . ($company->workingAddress ?? 'N/A'));
            $this->line('Registration Date: ' . ($company->registrationDate ?? 'N/A'));
            $this->line('Start Date: ' . ($company->startDate ?? 'N/A'));
            $this->line('End Date: ' . ($company->endDate ?? 'N/A'));
            $this->line('Suspension Date: ' . ($company->suspensionDate ?? 'N/A'));
            $this->line('Resumption Date: ' . ($company->resumptionDate ?? 'N/A'));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
