<?php

namespace App\Services\AzureDocumentIntelligence\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * DTO for Azure Document Intelligence analyzeResult.
 *
 * @property string               $apiVersion
 * @property string               $modelId
 * @property ?string              $content
 * @property string               $contentFormat
 * @property InvoiceDocumentDTO[] $documents
 */
final class AnalyzeResult extends BaseDataDTO
{
    public function __construct(
        public readonly string $apiVersion,
        public readonly string $modelId,
        public readonly string $contentFormat,
        public readonly array $documents,
        public readonly ?string $content = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'apiVersion'    => $this->apiVersion,
            'modelId'       => $this->modelId,
            'content'       => $this->content,
            'contentFormat' => $this->contentFormat,
            'documents'     => array_map(fn ($doc) => $doc->toArray(), $this->documents),
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            apiVersion: $data['apiVersion'],
            modelId: $data['modelId'],
            content: $data['content'] ?? null,
            contentFormat: $data['contentFormat'],
            documents: array_map(
                function ($doc) {
                    unset($doc['boundingRegions']);

                    return InvoiceDocumentDTO::fromArray($doc);
                },
                $data['documents'] ?? []
            )
        );
    }
}
