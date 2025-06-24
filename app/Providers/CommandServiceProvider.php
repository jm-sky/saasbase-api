<?php

namespace App\Providers;

use App\Services\AzureDocumentIntelligence\Commands\AnalyzeDocumentCommand;
use App\Services\IbanInfo\Commands\IbanInfoCommand;
use App\Services\MfLookup\Commands\MfLookupCommand;
use App\Services\NBP\Commands\ImportExchangeRatesCommand;
use App\Services\RegonLookup\Commands\RegonLookupCommand;
use App\Services\Signatures\Commands\VerifyXmlSignatureCommand;
use App\Services\ViesLookup\Commands\ViesLookupCommand;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                AnalyzeDocumentCommand::class,
                IbanInfoCommand::class,
                ImportExchangeRatesCommand::class,
                MfLookupCommand::class,
                RegonLookupCommand::class,
                VerifyXmlSignatureCommand::class,
                ViesLookupCommand::class,
            ]);
        }
    }
}
