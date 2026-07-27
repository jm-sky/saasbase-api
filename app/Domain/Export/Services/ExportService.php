<?php

namespace App\Domain\Export\Services;

use App\Domain\Export\DTOs\ExportConfigDTO;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportService
{
    /**
     * Download an Excel export for the given export class and config.
     *
     * @return BinaryFileResponse
     */
    public function download(string $exportClass, ExportConfigDTO $config, string $filename)
    {
        return Excel::download(
            new $exportClass(
                filters: $config->filters,
                columns: $config->columns,
                formatting: $config->formatting
            ),
            $filename
        );
    }
}
