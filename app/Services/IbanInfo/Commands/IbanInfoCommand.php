<?php

namespace App\Services\IbanInfo\Commands;

use App\Services\IbanInfo\IbanInfoService;
use Illuminate\Console\Command;

class IbanInfoCommand extends Command
{
    protected $signature = 'iban:info {iban}';

    protected $description = 'Lookup bank details by IBAN and print result.';

    public function handle(IbanInfoService $service): int
    {
        $iban = $this->argument('iban');

        $this->info('Looking up bank details for ' . $iban . '...');

        try {
            $ibanInfo = $service->getBankInfoFromIban($iban);

            if (null === $ibanInfo) {
                $this->warn('No bank found for given IBAN.');

                return self::FAILURE;
            }

            $this->info('IBAN Details:');
            $this->line('- Bank Name         : ' . $ibanInfo->bankName);
            $this->line('- Branch Name       : ' . $ibanInfo->branchName);
            $this->line('- SWIFT             : ' . $ibanInfo->swift);
            $this->line('- Bank Code         : ' . $ibanInfo->bankCode);
            $this->line('- Routing Code      : ' . $ibanInfo->routingCode);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
