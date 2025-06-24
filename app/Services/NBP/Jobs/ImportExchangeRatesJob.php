<?php

namespace App\Services\NBP\Jobs;

use App\Services\NBP\Actions\CreateExchangeRatesFromImport;
use App\Services\NBP\Enums\NBPTableEnum;
use App\Services\NBP\NBPService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportExchangeRatesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected ?Carbon $date = null,
        protected NBPTableEnum $table = NBPTableEnum::A
    ) {
    }

    public function handle(NBPService $nbpService): void
    {
        $date = $this->date ?? Carbon::yesterday();

        // Skip weekends
        if ($date->isSaturday() || $date->isSunday()) {
            Log::info("Skipping NBP import for weekend date: {$date->format('Y-m-d')}");

            return;
        }

        try {
            Log::info("Starting NBP import for {$date->format('Y-m-d')}");

            $rates = $nbpService->getExchangeRates($this->table, $date);

            if ($rates->isEmpty()) {
                Log::warning("No exchange rates found for {$date->format('Y-m-d')}");

                return;
            }

            $imported = CreateExchangeRatesFromImport::handle($rates);

            Log::info("NBP import completed. Imported {$imported} rates for {$date->format('Y-m-d')}");
        } catch (\Exception $e) {
            Log::error("NBP import failed for {$date->format('Y-m-d')}: " . $e->getMessage());

            throw $e;
        }
    }
}
