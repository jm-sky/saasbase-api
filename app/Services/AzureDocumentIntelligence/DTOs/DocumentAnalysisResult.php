<?php

namespace App\Services\AzureDocumentIntelligence\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Services\AzureDocumentIntelligence\Enums\DocumentAnalysisStatus;

/**
 * DTO for Azure Document Analysis result.
 *
 * @property DocumentAnalysisStatus $status
 * @property ?AnalyzeResult         $analyzeResult
 * @property ?string                $error
 */
class DocumentAnalysisResult extends BaseDataDTO
{
    public function __construct(
        public readonly DocumentAnalysisStatus $status,
        public readonly ?AnalyzeResult $analyzeResult = null,
        public readonly ?string $error = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'status'        => $this->status->value,
            'analyzeResult' => $this->analyzeResult?->toArray(),
            'error'         => $this->error,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            status: DocumentAnalysisStatus::from($data['status']),
            analyzeResult: isset($data['analyzeResult']) ? AnalyzeResult::fromArray($data['analyzeResult']) : null,
            error: $data['error']['message'] ?? ($data['error'] ?? null)
        );
    }
}
