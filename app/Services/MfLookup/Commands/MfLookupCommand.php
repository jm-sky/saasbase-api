<?php

namespace App\Services\MfLookup\Commands;

use App\Services\MfLookup\Services\MfLookupService;
use Illuminate\Console\Command;

class MfLookupCommand extends Command
{
    protected $signature = 'mf:lookup {nip} {--force}';

    protected $description = 'Lookup company details by NIP from Ministry of Finance database and print result.';

    public function handle(MfLookupService $service): int
    {
        $nip   = $this->argument('nip');
        $force = $this->option('force');

        $this->info('Looking up company details in MF for ' . $nip . ($force ? ' (force)' : '') . '...');

        try {
            $company = $service->findByNip($nip, force: $force);

            if (null === $company) {
                $this->warn('No company found for given NIP.');

                return self::FAILURE;
            }

            $this->info('Company Details:');
            $this->line('- Name              : ' . $company->name);
            $this->line('- NIP               : ' . $company->nip);
            $this->line('- REGON             : ' . ($company->regon ?? 'N/A'));
            $this->line('- KRS               : ' . ($company->krs ?? 'N/A'));
            $this->line('- Residence Address : ' . ($company->residenceAddress ?? 'N/A'));
            $this->line('- Working Address   : ' . ($company->workingAddress ?? 'N/A'));
            $this->line('- VAT Status        : ' . $company->vatStatus->value);

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
