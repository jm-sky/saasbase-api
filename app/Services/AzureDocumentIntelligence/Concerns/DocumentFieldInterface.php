<?php

namespace App\Services\AzureDocumentIntelligence\Concerns;

interface DocumentFieldInterface
{
    public function getType(): string;

    public function getValue(): mixed;

    public function getConfidence(): float;

    public function toArray(): array;

    public static function fromArray(array $data): static;

    public function validate(): void;
}
