<?php

namespace App\Console\Commands;

use App\Domain\Template\Services\PdfEngineFactory;
use Illuminate\Console\Command;

class PdfEngineStatusCommand extends Command
{
    protected $signature = 'pdf:engine-status';

    protected $description = 'Show PDF engine status and configuration';

    public function handle(): void
    {
        $this->info('PDF Engine Status');
        $this->info('==================');

        $defaultEngine = config('pdf.default', 'mpdf');
        $this->info("Default engine: {$defaultEngine}");
        $this->newLine();

        $availableEngines = PdfEngineFactory::getAvailableEngines();

        if (empty($availableEngines)) {
            $this->error('No PDF engines are available!');

            return;
        }

        $this->info('Available engines:');

        foreach ($availableEngines as $key => $name) {
            $status = $key === $defaultEngine ? ' (default)' : '';
            $this->line("  • {$name}{$status}");
        }

        $this->newLine();
        $this->info('Engine configurations:');

        foreach (array_keys($availableEngines) as $engineName) {
            $this->line("  {$engineName}:");

            try {
                $engine = PdfEngineFactory::create($engineName);
                $config = $engine->getConfig();

                foreach ($config as $key => $value) {
                    $displayValue = is_array($value) ? json_encode($value) : $value;
                    $this->line("    {$key}: {$displayValue}");
                }
            } catch (\Exception $e) {
                $this->error("    Error loading config: {$e->getMessage()}");
            }
            $this->newLine();
        }
    }
}
