<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function commands(): void
    {
        $this->load(base_path('app/Domain/CompanyLookup/Commands'));
        // Load other domain commands if needed
        require base_path('routes/console.php');
    }
}
