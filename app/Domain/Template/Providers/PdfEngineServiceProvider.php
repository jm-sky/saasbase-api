<?php

namespace App\Domain\Template\Providers;

use App\Domain\Template\Contracts\PdfEngineInterface;
use App\Domain\Template\Services\PdfEngineFactory;
use Illuminate\Support\ServiceProvider;

class PdfEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PdfEngineInterface::class, function () {
            return PdfEngineFactory::create();
        });
    }
}
