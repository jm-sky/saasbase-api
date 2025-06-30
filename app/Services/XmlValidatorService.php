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

        // Dynamicznie generuj XSD z odpowiednią przestrzenią nazw
        $dynamicXsd = $this->generateDynamicXsd($xsdPath);

        if (!$dom->schemaValidateSource($dynamicXsd)) {
            throw new \RuntimeException('XML niezgodny z XSD: ' . $this->formatErrors());
        }

        libxml_clear_errors();
    }

    protected function generateDynamicXsd(string $xsdPath): string
    {
        $xsdContent  = file_get_contents($xsdPath);
        $frontendUrl = config('app.frontend_url');

        // Zamień hardkodowaną przestrzeń nazw na dynamiczną
        return str_replace(
            'https://saasbase.madeyski.org/xml/identity/v1',
            $frontendUrl . '/xml/identity/v1',
            $xsdContent
        );
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
