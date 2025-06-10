<?php

namespace App\Services\AzureDocumentIntelligence\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * DTO for Azure Document Analysis result.
 *
 * @property DocumentAnalysisStatus $status
 * @property array|null             $fields
 * @property string|null            $error
 */
class DocumentAnalysisResult extends BaseDataDTO
{
    public function __construct(
        public readonly DocumentAnalysisStatus $status,
        public readonly ?array $fields = null,
        public readonly ?string $error = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'fields' => $this->fields,
            'error'  => $this->error,
        ];
    }
}
