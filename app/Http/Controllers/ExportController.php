<?php

namespace App\Http\Controllers;

use App\Domain\Export\DTOs\ExportConfigDTO;
use App\Domain\Export\Services\ExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(
        private readonly ExportService $exportService
    ) {
    }

    /**
     * Handle an export request for a given type.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request, string $type)
    {
        $config = new ExportConfigDTO(
            filters: $request->all(),
            columns: $request->get('columns', []),
            formatting: $request->get('formatting', [])
        );

        return $this->exportService->download(
            exportClass: $this->getExportClass($type),
            config: $config,
            filename: "{$type}.xlsx"
        );
    }

    private function getExportClass(string $type): string
    {
        return match ($type) {
            'tasks' => \App\Domain\Export\Exports\TasksExport::class,
            default => throw new \InvalidArgumentException("Unsupported export type: {$type}")
        };
    }
}
