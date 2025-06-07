<?php

namespace App\Services;

class XmlValidatorService
{
    public function validate(string $xmlContent, string $xsdPath): void
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();

        if (!$dom->loadXML($xmlContent)) {
            throw new \RuntimeException('Niepoprawna struktura XML: ' . $this->formatErrors());
        }

        if (!$dom->schemaValidate($xsdPath)) {
            throw new \RuntimeException('XML niezgodny z XSD: ' . $this->formatErrors());
        }

        libxml_clear_errors();
    }

    protected function formatErrors(): string
    {
        $errors = libxml_get_errors();

        return collect($errors)
            ->map(fn ($error) => trim($error->message))
            ->implode('; ')
        ;
    }
}
