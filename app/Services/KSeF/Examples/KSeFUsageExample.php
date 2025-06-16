<?php

namespace App\Services\KSeF\Examples;

use App\Services\KSeF\DTOs\QueryCriteriaDTO;
use App\Services\KSeF\DTOs\QueryInvoiceRequestDTO;
use App\Services\KSeF\Enums\InvoiceType;
use App\Services\KSeF\Enums\SubjectType;
use App\Services\KSeF\Services\KSeFService;
use Carbon\Carbon;

/**
 * Example usage of KSeF service.
 *
 * This class demonstrates how to use the KSeF integration
 * to interact with the Polish National e-Invoice System
 */
class KSeFUsageExample
{
    protected KSeFService $ksefService;

    public function __construct(string $encryptedToken)
    {
        $this->ksefService = new KSeFService($encryptedToken);
    }

    /**
     * Example: Initialize session and check permissions.
     */
    public function initializeSessionExample(string $encryptedToken): void
    {
        // Initialize session
        $sessionResponse = $this->ksefService->initSession($encryptedToken);

        echo "Session initialized successfully!\n";
        echo "Reference Number: {$sessionResponse->referenceNumber}\n";
        echo "Session Token: {$sessionResponse->sessionToken->token}\n";
        echo "Context Type: {$sessionResponse->sessionToken->context->contextIdentifier->type}\n";
        echo "Context Identifier: {$sessionResponse->sessionToken->context->contextIdentifier->identifier}\n";

        // Check permissions
        $permissions = $sessionResponse->sessionToken->context->credentialsRoleList;
        echo "\nPermissions:\n";

        foreach ($permissions as $permission) {
            echo "- Type: {$permission->type}, Role: {$permission->roleType}, Description: {$permission->roleDescription}\n";
        }
    }

    /**
     * Example: Search invoices by date range.
     */
    public function searchInvoicesByDateExample(): void
    {
        $dateFrom = Carbon::now()->subDays(30);
        $dateTo   = Carbon::now();

        $response = $this->ksefService->searchInvoicesByDateRange(
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            pageSize: 20,
            pageOffset: 0,
            invoiceTypes: [InvoiceType::VAT->value, InvoiceType::KOR->value]
        );

        echo "Found {$response->numberOfElements} invoices\n";
        echo "Page: {$response->pageOffset}, Size: {$response->pageSize}\n";

        foreach ($response->invoiceHeaderList as $invoice) {
            echo "\nInvoice: {$invoice->invoiceReferenceNumber}\n";
            echo "KSeF Number: {$invoice->ksefReferenceNumber}\n";
            echo "Type: {$invoice->invoiceType}\n";
            echo "Date: {$invoice->invoicingDate->format('Y-m-d')}\n";
            echo "Amount: {$invoice->gross} {$invoice->currency}\n";
            echo "Issuer: {$invoice->subjectBy->issuedByName}\n";
            echo "Recipient: {$invoice->subjectTo->issuedToName}\n";
        }
    }

    /**
     * Example: Search invoices by NIP number.
     */
    public function searchInvoicesByNipExample(string $nip): void
    {
        $response = $this->ksefService->searchInvoicesByIdentifier(
            identifiers: [$nip],
            identifierType: 'by', // Search by issuer NIP
            pageSize: 10,
            pageOffset: 0
        );

        echo "Found {$response->numberOfElements} invoices issued by NIP: {$nip}\n";

        foreach ($response->invoiceHeaderList as $invoice) {
            echo "\n- {$invoice->invoiceReferenceNumber} ({$invoice->gross} {$invoice->currency})\n";
        }
    }

    /**
     * Example: Complex search with custom criteria.
     */
    public function complexSearchExample(): void
    {
        $criteria = new QueryCriteriaDTO(
            subjectType: SubjectType::SUBJECT_BY->value,
            invoicingDateFrom: Carbon::now()->subDays(7),
            invoicingDateTo: Carbon::now(),
            invoiceTypes: [InvoiceType::VAT->value],
            amountFrom: '100.00',
            amountTo: '10000.00',
            currencyCode: 'PLN',
            faP17Annotation: false
        );

        $queryData = new QueryInvoiceRequestDTO(
            queryCriteria: $criteria,
            pageSize: 50,
            pageOffset: 0
        );

        $response = $this->ksefService->searchInvoices($queryData);

        echo "Complex search found {$response->numberOfElements} invoices\n";
        echo "Criteria: VAT invoices from last 7 days, amount 100-10000 PLN\n";
    }
}
