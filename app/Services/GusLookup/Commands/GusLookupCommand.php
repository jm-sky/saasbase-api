<?php

namespace App\Services\GusLookup\Commands;

use App\Services\GusLookup\Services\GusLookupService;
use Illuminate\Console\Command;

class GusLookupCommand extends Command
{
    protected $signature = 'gus:lookup {nip}';

    protected $description = 'Lookup company details by NIP and print result.';

    public function handle(GusLookupService $service): int
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

            if ($company->mainPkdCode) {
                $this->line('Main PKD: ' . $company->mainPkdCode . ' - ' . $company->mainPkdName);
            }

            if (!empty($company->pkdCodes)) {
                $this->line('PKD Codes:');

                foreach ($company->pkdCodes as $index => $code) {
                    $this->line('  - ' . $code . ' - ' . ($company->pkdNames[$index] ?? 'N/A'));
                }
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
