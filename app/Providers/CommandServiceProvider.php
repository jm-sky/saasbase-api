<?php

namespace App\Providers;

use App\Services\AzureDocumentIntelligence\Commands\AnalyzeDocumentCommand;
use App\Services\IbanInfo\Commands\IbanInfoCommand;
use App\Services\MfLookup\Commands\MfLookupCommand;
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
                RegonLookupCommand::class,
                ViesLookupCommand::class,
                MfLookupCommand::class,
                IbanInfoCommand::class,
                AnalyzeDocumentCommand::class,
                VerifyXmlSignatureCommand::class,
            ]);
        }
    }
}
