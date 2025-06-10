<?php

namespace App\Services\AzureDocumentIntelligence\Enums;

/**
 * Enum for Azure Document Analysis status.
 */
enum DocumentAnalysisStatus: string implements \JsonSerializable
{
    case NOT_STARTED = 'notStarted';
    case RUNNING     = 'running';
    case SUCCEEDED   = 'succeeded';
    case FAILED      = 'failed';

    public function toArray(): array
    {
        return ['status' => $this->value];
    }

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }
}
