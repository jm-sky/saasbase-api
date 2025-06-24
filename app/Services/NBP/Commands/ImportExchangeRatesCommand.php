<?php

namespace App\Services\NBP\Commands;

use App\Services\NBP\Actions\CreateExchangeRatesFromImport;
use App\Services\NBP\Enums\NBPTableEnum;
use App\Services\NBP\NBPService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportExchangeRatesCommand extends Command
{
    protected $signature = 'nbp:import-rates
                            {--date= : Specific date to import (Y-m-d format)}
                            {--days=1 : Number of days back to import}
                            {--table=A : NBP table (A, B, or C)}
                            {--force : Force reimport existing rates}';

    protected $description = 'Import exchange rates from NBP API';

    public function handle(NBPService $nbpService): int
    {
        $this->info('Starting NBP exchange rates import...');

        try {
            $table = NBPTableEnum::from($this->option('table'));
            $force = $this->option('force');

            if ($specificDate = $this->option('date')) {
                $date = Carbon::parse($specificDate);
                $this->importForDate($nbpService, $date, $table, $force);
            } else {
                $days = (int) $this->option('days');
                $this->importRecentDays($nbpService, $days, $table, $force);
            }

            $this->info('Exchange rates import completed successfully!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    protected function importForDate(NBPService $nbpService, Carbon $date, NBPTableEnum $table, bool $force): void
    {
        $this->info("Importing rates for {$date->format('Y-m-d')}...");

        $rates = $nbpService->getExchangeRates($table, $date);

        if ($rates->isEmpty()) {
            $this->warn("No rates found for {$date->format('Y-m-d')}");

            return;
        }

        $imported = 0;
        $skipped  = 0;

        CreateExchangeRatesFromImport::handle($rates, $force, $imported, $skipped);

        $this->info("Imported: {$imported}, Skipped: {$skipped}");
    }

    protected function importRecentDays(NBPService $nbpService, int $days, NBPTableEnum $table, bool $force): void
    {
        for ($i = 0; $i < $days; ++$i) {
            $date = Carbon::now()->subDays($i);

            // Skip weekends for NBP data
            if ($date->isSaturday() || $date->isSunday()) {
                continue;
            }

            try {
                $this->importForDate($nbpService, $date, $table, $force);
            } catch (\Exception $e) {
                $this->warn("Failed to import for {$date->format('Y-m-d')}: " . $e->getMessage());
            }
        }
    }
}
