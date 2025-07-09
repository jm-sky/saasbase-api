<?php

namespace App\Domain\Template\Services;

use App\Domain\Financial\DTOs\InvoicePartyDTO;
use App\Domain\Financial\DTOs\InvoicePaymentDTO;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Template\DTOs\InvoiceDataTemplateDTO;
use App\Domain\Template\DTOs\InvoicePartyTemplateDTO;

class InvoiceToTemplateTransformer
{
    public function __construct(
        private CurrencyFormatterService $currencyFormatter,
        private MediaUrlService $mediaUrlService
    ) {
    }

    /**
     * Transform Invoice model to template DTO.
     */
    public function transform(Invoice $invoice): InvoiceDataTemplateDTO
    {
        return new InvoiceDataTemplateDTO(
            id: $invoice->id,
            number: $invoice->number,
            issueDate: $this->formatDate($invoice->issue_date),
            dueDate: $this->formatDate($invoice->payment->dueDate ?? $invoice->issue_date), // fallback to issue_date
            seller: $this->transformParty($invoice->seller),
            buyer: $this->transformParty($invoice->buyer),
            lines: $this->transformLines($invoice->body->lines ?? []),
            vatSummary: $this->transformVatSummary($invoice->body->vatSummary ?? []),
            formattedTotalNet: $this->currencyFormatter->format($invoice->total_net, $invoice->currency),
            formattedTotalTax: $this->currencyFormatter->format($invoice->total_tax, $invoice->currency),
            formattedTotalGross: $this->currencyFormatter->format($invoice->total_gross, $invoice->currency),
            currency: $invoice->currency,
            payment: $this->transformPayment($invoice->payment),
            options: $invoice->options->toArray(),
            description: $invoice->body->description ?? null,
            logoUrl: $this->getLogoUrl($invoice),
        );
    }

    /**
     * Transform party DTO (seller/buyer).
     */
    private function transformParty(InvoicePartyDTO $partyDTO): InvoicePartyTemplateDTO
    {
        return new InvoicePartyTemplateDTO(
            name: $partyDTO->name,
            taxId: $partyDTO->taxId,
            address: $this->formatAddress($partyDTO->address),
            country: $partyDTO->country,
            iban: $partyDTO->iban,
            email: $partyDTO->email,
            phone: null, // Add phone if available in DTO
        );
    }

    /**
     * Transform invoice lines to match template expectations.
     */
    private function transformLines(array $lines): array
    {
        return array_map(function ($line) {
            // Handle both InvoiceLineDTO objects and arrays
            if (is_object($line) && method_exists($line, 'toArray')) {
                $lineData = $line->toArray();
            } else {
                $lineData = is_array($line) ? $line : [];
            }

            $vatRate    = $lineData['vatRate']['rate'] ?? 0;
            $unitPrice  = $lineData['unitPrice'] ?? '0';
            $quantity   = $lineData['quantity'] ?? 0;
            $totalNet   = $lineData['totalNet'] ?? '0';
            $totalVat   = $lineData['totalVat'] ?? '0';
            $totalGross = $lineData['totalGross'] ?? '0';

            return [
                'description'         => $lineData['description'] ?? '',
                'formattedQuantity'   => number_format((float) $quantity, 2),
                'formattedUnitPrice'  => $this->currencyFormatter->formatWithoutCurrency($unitPrice),
                'formattedTotalNet'   => $this->currencyFormatter->formatWithoutCurrency($totalNet),
                'formattedTotalVat'   => $this->currencyFormatter->formatWithoutCurrency($totalVat),
                'formattedTotalGross' => $this->currencyFormatter->formatWithoutCurrency($totalGross),
                'vatRateName'         => 'Standard VAT',
                'vatRateValue'        => (float) $vatRate,
            ];
        }, $lines);
    }

    /**
     * Transform VAT summary.
     */
    private function transformVatSummary(array $vatSummary): array
    {
        return array_map(function ($vatLine) {
            if (is_object($vatLine) && method_exists($vatLine, 'toArray')) {
                $vatData = $vatLine->toArray();
            } else {
                $vatData = is_array($vatLine) ? $vatLine : [];
            }

            $vatRate     = $vatData['vatRate'] ?? 0;
            $netAmount   = $vatData['netAmount'] ?? '0';
            $vatAmount   = $vatData['vatAmount'] ?? '0';
            $grossAmount = $vatData['grossAmount'] ?? '0';

            return [
                'vatRateName'    => 'Standard VAT',
                'vatRateValue'   => (float) $vatRate,
                'formattedNet'   => $this->currencyFormatter->formatWithoutCurrency($netAmount),
                'formattedVat'   => $this->currencyFormatter->formatWithoutCurrency($vatAmount),
                'formattedGross' => $this->currencyFormatter->formatWithoutCurrency($grossAmount),
            ];
        }, $vatSummary);
    }

    /**
     * Transform payment DTO.
     */
    private function transformPayment(InvoicePaymentDTO $paymentDTO): array
    {
        return [
            'method'      => $paymentDTO->method?->name ?? null,
            'dueDate'     => isset($paymentDTO->dueDate) ? $this->formatDate($paymentDTO->dueDate) : null,
            'bankAccount' => $paymentDTO->bankAccount ?? null,
            'terms'       => $paymentDTO->terms ?? null,
            'reference'   => $paymentDTO->reference ?? null,
        ];
    }

    /**
     * Format address data.
     */
    private function formatAddress(?string $address): ?string
    {
        return $address;
    }

    /**
     * Format date.
     */
    private function formatDate($date): string
    {
        if (!$date) {
            return '';
        }

        if ($date instanceof \Carbon\Carbon) {
            return $date->format('Y-m-d');
        }

        return $date;
    }

    /**
     * Get logo URL if available.
     */
    private function getLogoUrl(Invoice $invoice): ?string
    {
        // Check if invoice has logo media attached
        $logoMedia = $invoice->getFirstMedia('logo');

        if ($logoMedia) {
            return $this->mediaUrlService->getSecureUrl($logoMedia);
        }

        // Fallback to tenant logo
        $tenant = $invoice->tenant;

        if ($tenant && method_exists($tenant, 'getFirstMedia')) {
            $tenantLogo = $tenant->getFirstMedia('logo');

            if ($tenantLogo) {
                return $this->mediaUrlService->getSecureUrl($tenantLogo);
            }
        }

        return null;
    }
}
