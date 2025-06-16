<?php

namespace App\Services\KSeF\Services;

use App\Services\KSeF\DTOs\InitSessionResponseDTO;
use App\Services\KSeF\DTOs\QueryCredentialRequestDTO;
use App\Services\KSeF\DTOs\QueryCriteriaDTO;
use App\Services\KSeF\DTOs\QueryInvoiceRequestDTO;
use App\Services\KSeF\DTOs\QueryInvoiceResponseDTO;
use App\Services\KSeF\DTOs\SendInvoiceRequestDTO;
use App\Services\KSeF\DTOs\SendInvoiceResponseDTO;
use App\Services\KSeF\DTOs\StatusInvoiceResponseDTO;
use App\Services\KSeF\DTOs\TerminateSessionResponseDTO;
use App\Services\KSeF\Integrations\KSeFApiConnector;
use App\Services\KSeF\Integrations\Requests\InitSessionSignedRequest;
use App\Services\KSeF\Integrations\Requests\InitSessionTokenRequest;
use App\Services\KSeF\Integrations\Requests\InvoiceGetRequest;
use App\Services\KSeF\Integrations\Requests\InvoiceStatusRequest;
use App\Services\KSeF\Integrations\Requests\QueryCredentialContextSyncRequest;
use App\Services\KSeF\Integrations\Requests\QueryCredentialSyncRequest;
use App\Services\KSeF\Integrations\Requests\QueryInvoiceSyncRequest;
use App\Services\KSeF\Integrations\Requests\SendInvoiceRequest;
use App\Services\KSeF\Integrations\Requests\TerminateSessionRequest;
use Carbon\Carbon;

class KSeFService
{
    protected KSeFApiConnector $connector;

    public function __construct(?string $encryptedToken = null)
    {
        $this->connector = new KSeFApiConnector($encryptedToken);
    }

    public function initSession(string $encryptedToken): InitSessionResponseDTO
    {
        $request  = new InitSessionTokenRequest($encryptedToken);
        $response = $this->connector->send($request);

        return $response->dto();
    }

    public function checkTokenPermissions(): array
    {
        // Token permissions are included in the session initialization response
        // The credentialsRoleList contains the permissions
        $sessionResponse = $this->getSessionInfo();

        return $sessionResponse->sessionToken->context->credentialsRoleList;
    }

    public function searchInvoices(QueryInvoiceRequestDTO $queryData): QueryInvoiceResponseDTO
    {
        $request  = new QueryInvoiceSyncRequest($queryData);
        $response = $this->connector->send($request);

        return $response->dto();
    }

    /**
     * Search invoices by basic criteria.
     */
    public function searchInvoicesByDateRange(
        Carbon $dateFrom,
        Carbon $dateTo,
        int $pageSize = 10,
        int $pageOffset = 0,
        ?string $subjectType = null,
        ?array $invoiceTypes = null
    ): QueryInvoiceResponseDTO {
        $criteria = new QueryCriteriaDTO(
            subjectType: $subjectType,
            invoicingDateFrom: $dateFrom,
            invoicingDateTo: $dateTo,
            invoiceTypes: $invoiceTypes
        );

        $queryData = new QueryInvoiceRequestDTO(
            queryCriteria: $criteria,
            pageSize: $pageSize,
            pageOffset: $pageOffset
        );

        return $this->searchInvoices($queryData);
    }

    /**
     * Search invoices by NIP/identifier.
     */
    public function searchInvoicesByIdentifier(
        array $identifiers,
        string $identifierType = 'by', // 'by' or 'to'
        int $pageSize = 10,
        int $pageOffset = 0
    ): QueryInvoiceResponseDTO {
        $criteria = new QueryCriteriaDTO(
            subjectByIdentifierList: 'by' === $identifierType ? $identifiers : null,
            subjectToIdentifierList: 'to' === $identifierType ? $identifiers : null
        );

        $queryData = new QueryInvoiceRequestDTO(
            queryCriteria: $criteria,
            pageSize: $pageSize,
            pageOffset: $pageOffset
        );

        return $this->searchInvoices($queryData);
    }

    protected function getSessionInfo(): InitSessionResponseDTO
    {
        // This would need to be cached or retrieved from the current session
        // For now, we'll throw an exception to indicate this needs implementation
        throw new \BadMethodCallException('Session info retrieval not yet implemented');
    }

    /**
     * Initialize session with signed document.
     */
    public function initSessionSigned(string $signedDocument): InitSessionResponseDTO
    {
        $request  = new InitSessionSignedRequest($signedDocument);
        $response = $this->connector->send($request);

        return $response->dto();
    }

    /**
     * Terminate current session.
     */
    public function terminateSession(): TerminateSessionResponseDTO
    {
        $request  = new TerminateSessionRequest();
        $response = $this->connector->send($request);

        return $response->dto();
    }

    /**
     * Send invoice to KSeF.
     */
    public function sendInvoice(SendInvoiceRequestDTO $invoiceData): SendInvoiceResponseDTO
    {
        $request  = new SendInvoiceRequest($invoiceData);
        $response = $this->connector->send($request);

        return $response->dto();
    }

    /**
     * Get invoice status.
     */
    public function getInvoiceStatus(string $elementReferenceNumber): StatusInvoiceResponseDTO
    {
        $request  = new InvoiceStatusRequest($elementReferenceNumber);
        $response = $this->connector->send($request);

        return $response->dto();
    }

    /**
     * Get invoice by KSeF reference number.
     */
    public function getInvoice(string $ksefReferenceNumber): mixed
    {
        $request  = new InvoiceGetRequest($ksefReferenceNumber);
        $response = $this->connector->send($request);

        // This endpoint returns raw invoice data (XML/JSON)
        return $response->body();
    }

    /**
     * Query credentials.
     */
    public function queryCredentials(QueryCredentialRequestDTO $queryData): mixed
    {
        $request  = new QueryCredentialSyncRequest($queryData);
        $response = $this->connector->send($request);

        // Return raw response for now - would need proper DTO
        return $response->json();
    }

    /**
     * Query credential context.
     */
    public function queryCredentialContext(
        ?string $contextNip = null,
        ?string $sourceIdentifier = null,
        ?string $targetIdentifier = null
    ): mixed {
        $request  = new QueryCredentialContextSyncRequest($contextNip, $sourceIdentifier, $targetIdentifier);
        $response = $this->connector->send($request);

        // Return raw response for now - would need proper DTO
        return $response->json();
    }
}
