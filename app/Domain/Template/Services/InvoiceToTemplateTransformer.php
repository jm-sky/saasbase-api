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
            items: $this->transformItems($invoice->body->lines ?? []),
            totalNet: $this->currencyFormatter->formatWithoutCurrency($invoice->total_net),
            totalTax: $this->currencyFormatter->formatWithoutCurrency($invoice->total_tax),
            totalGross: $this->currencyFormatter->formatWithoutCurrency($invoice->total_gross),
            currency: $invoice->currency,
            payment: $this->transformPayment($invoice->payment),
            options: $invoice->options->toArray(),
            notes: $invoice->body->description ?? null,
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
     * Transform invoice items.
     */
    private function transformItems(array $lines): array
    {
        return array_map(function ($line) {
            // Handle both InvoiceLineDTO objects and arrays
            if (is_object($line) && method_exists($line, 'toArray')) {
                $lineData = $line->toArray();
            } else {
                $lineData = is_array($line) ? $line : [];
            }

            return [
                'name'        => $lineData['description'] ?? '',
                'description' => $lineData['description'] ?? '',
                'quantity'    => $lineData['quantity'] ?? 0,
                'unit'        => '', // Unit not available in InvoiceLineDTO
                'priceNet'    => $this->currencyFormatter->formatWithoutCurrency($lineData['unitPrice'] ?? '0'),
                'vatRate'     => $lineData['vatRate']['rate'] ?? 0,
                'vatAmount'   => $this->currencyFormatter->formatWithoutCurrency($lineData['totalVat'] ?? '0'),
                'priceGross'  => $this->currencyFormatter->formatWithoutCurrency($lineData['totalGross'] ?? '0'),
                'totalNet'    => $this->currencyFormatter->formatWithoutCurrency($lineData['totalNet'] ?? '0'),
                'totalTax'    => $this->currencyFormatter->formatWithoutCurrency($lineData['totalVat'] ?? '0'),
                'totalGross'  => $this->currencyFormatter->formatWithoutCurrency($lineData['totalGross'] ?? '0'),
            ];
        }, $lines);
    }

    /**
     * Transform payment DTO.
     */
    private function transformPayment(InvoicePaymentDTO $paymentDTO): array
    {
        return [
            'method'      => $paymentDTO->method ?? null,
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
