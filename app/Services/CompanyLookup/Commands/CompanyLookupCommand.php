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
            $this->line('REGON: ' . ($company->regon ?? 'N/A'));
            $this->line('KRS: ' . ($company->krs ?? 'N/A'));
            $this->line('Residence Address: ' . ($company->residenceAddress ?? 'N/A'));
            $this->line('Working Address: ' . ($company->workingAddress ?? 'N/A'));
            $this->line('VAT Status: ' . $company->vatStatus->value);

            if (!empty($company->accountNumbers)) {
                $this->line('Account Numbers:');
                foreach ($company->accountNumbers as $account) {
                    $this->line('  - ' . $account);
                }
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
