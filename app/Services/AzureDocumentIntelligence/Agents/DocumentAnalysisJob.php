<?php

namespace App\Services\AzureDocumentIntelligence\Agents;

use App\Domain\Tenant\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DocumentAnalysisJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $filePath,
        protected ?string $modelId = null,
        protected ?int $tenantId = null
    ) {
    }

    public function handle(DocumentAnalysisAgent $agent): void
    {
        // Bypass tenant if needed
        if ($this->tenantId) {
            Tenant::bypassTenant($this->tenantId, fn () => $this->processDocument($agent));
        } else {
            $this->processDocument($agent);
        }
    }

    protected function processDocument(DocumentAnalysisAgent $agent): void
    {
        $result = $agent->analyzeDocument($this->filePath, $this->modelId);
        // Store result in database or trigger notifications
        // Implementation depends on specific requirements
    }
}
