<?php

namespace App\Services\AzureDocumentIntelligence\Commands;

use App\Services\AzureDocumentIntelligence\DocumentAnalysisService;
use App\Services\AzureDocumentIntelligence\Exceptions\AzureDocumentIntelligenceException;
use Illuminate\Console\Command;

class AnalyzeDocumentCommand extends Command
{
    protected $signature = 'azure:analyze-document {file : Path to the PDF file} {--cache : Use cache for analysis} {--force : Force re-analysis}';

    protected $description = 'Analyze a PDF document using Azure Document Intelligence';

    public function handle(DocumentAnalysisService $service)
    {
        $filePath = $this->argument('file');
        $useCache = $this->option('cache');
        $force    = $this->option('force');

        if (!file_exists($filePath)) {
            $this->error('File does not exist: ' . $filePath);

            return 1;
        }

        try {
            $result = $useCache
                ? $service->analyzeWithCache($filePath, force: $force)
                : $service->analyze($filePath);
            $this->info(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return 0;
        } catch (AzureDocumentIntelligenceException $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error(json_encode($e->getContext(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return 1;
        }
    }
}
