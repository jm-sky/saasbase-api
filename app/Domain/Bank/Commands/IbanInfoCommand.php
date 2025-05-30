<?php

namespace App\Domain\Bank\Commands;

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
            $bankInfo = $service->getBankInfoFromIban($iban);

            if (null === $bankInfo) {
                $this->warn('No bank found for given IBAN.');

                return self::FAILURE;
            }

            $this->info('IBAN Details:');
            $this->line('- Bank Name         : ' . $bankInfo->bankName);
            $this->line('- Branch Name       : ' . $bankInfo->branchName);
            $this->line('- SWIFT             : ' . $bankInfo->swift);
            $this->line('- Bank Code         : ' . $bankInfo->bankCode);
            $this->line('- Routing Code      : ' . $bankInfo->routingCode);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
