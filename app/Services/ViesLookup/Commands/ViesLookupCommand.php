<?php

namespace App\Services\ViesLookup\Commands;

use App\Services\ViesLookup\Services\ViesLookupService;
use Illuminate\Console\Command;

class ViesLookupCommand extends Command
{
    protected $signature = 'vies:lookup {country_code} {vat_number} {--force}';

    protected $description = 'Lookup VAT details using VIES service';

    public function handle(ViesLookupService $service): int
    {
        $countryCode = $this->argument('country_code');
        $vatNumber   = $this->argument('vat_number');
        $force       = $this->option('force');

        $this->info('Looking up VAT details in VIES for ' . $countryCode . ' ' . $vatNumber . ($force ? ' (force)' : '') . '...');

        // try {
        $result = $service->findByVat($countryCode, $vatNumber, $force);

        dd($result->toCommonLookupData());

        if (null === $result) {
            $this->warn('No VAT details found.');

            return self::FAILURE;
        }

        $this->info('VAT Details:');
        $this->line('- Country Code : ' . $result->countryCode);
        $this->line('- VAT Number   : ' . $result->vatNumber);
        $this->line('- Valid        : ' . ($result->valid ? 'Yes' : 'No'));
        $this->line('- Company Name : ' . ($result->name ?? 'N/A'));
        $this->line('- Address      : ' . ($result->address ?? 'N/A'));

        return self::SUCCESS;
        // } catch (\Throwable $e) {
        //     $this->error('Error: ' . $e->getMessage());

        //     return self::FAILURE;
        // }
    }
}
