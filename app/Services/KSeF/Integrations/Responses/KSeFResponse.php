<?php

namespace App\Services\KSeF\Integrations\Responses;

use App\Services\KSeF\DTOs\ContextIdentifierDTO;
use App\Services\KSeF\DTOs\ContextNameDTO;
use App\Services\KSeF\DTOs\CredentialsRoleDTO;
use App\Services\KSeF\DTOs\InitSessionResponseDTO;
use App\Services\KSeF\DTOs\InvoiceHeaderDTO;
use App\Services\KSeF\DTOs\InvoiceStatusDTO;
use App\Services\KSeF\DTOs\QueryInvoiceResponseDTO;
use App\Services\KSeF\DTOs\SendInvoiceResponseDTO;
use App\Services\KSeF\DTOs\SessionContextDTO;
use App\Services\KSeF\DTOs\SessionTokenDTO;
use App\Services\KSeF\DTOs\StatusInvoiceResponseDTO;
use App\Services\KSeF\DTOs\SubjectByDTO;
use App\Services\KSeF\DTOs\SubjectToDTO;
use App\Services\KSeF\DTOs\TerminateSessionResponseDTO;
use Carbon\Carbon;
use Saloon\Http\Response;

class KSeFResponse extends Response
{
    public function dto(): InitSessionResponseDTO|QueryInvoiceResponseDTO|TerminateSessionResponseDTO|SendInvoiceResponseDTO|StatusInvoiceResponseDTO
    {
        $data = $this->json();

        // Check if this is an InitSession response
        if (isset($data['sessionToken'])) {
            return $this->createInitSessionDto($data);
        }

        // Check if this is a QueryInvoice response
        if (isset($data['invoiceHeaderList'])) {
            return $this->createQueryInvoiceDto($data);
        }

        // Check if this is a TerminateSession response
        if (isset($data['processingCode']) && !isset($data['elementReferenceNumber'])) {
            return $this->createTerminateSessionDto($data);
        }

        // Check if this is a SendInvoice response
        if (isset($data['elementReferenceNumber']) && !isset($data['invoiceStatus'])) {
            return $this->createSendInvoiceDto($data);
        }

        // Check if this is a StatusInvoice response
        if (isset($data['elementReferenceNumber'], $data['invoiceStatus'])) {
            return $this->createStatusInvoiceDto($data);
        }

        throw new \InvalidArgumentException('Unknown response format');
    }

    protected function createInitSessionDto(array $data): InitSessionResponseDTO
    {
        $sessionTokenData = $data['sessionToken'];
        $contextData      = $sessionTokenData['context'];

        $contextIdentifier = new ContextIdentifierDTO(
            type: $contextData['contextIdentifier']['type'],
            identifier: $contextData['contextIdentifier']['identifier']
        );

        $contextName = new ContextNameDTO(
            type: $contextData['contextName']['type'],
            tradeName: $contextData['contextName']['tradeName'] ?? null,
            fullName: $contextData['contextName']['fullName']
        );

        $credentialsRoles = array_map(
            fn (array $role) => new CredentialsRoleDTO(
                type: $role['type'],
                roleType: $role['roleType'],
                roleDescription: $role['roleDescription']
            ),
            $contextData['credentialsRoleList']
        );

        $sessionContext = new SessionContextDTO(
            contextIdentifier: $contextIdentifier,
            contextName: $contextName,
            credentialsRoleList: $credentialsRoles
        );

        $sessionToken = new SessionTokenDTO(
            token: $sessionTokenData['token'],
            context: $sessionContext
        );

        return new InitSessionResponseDTO(
            timestamp: Carbon::parse($data['timestamp']),
            referenceNumber: $data['referenceNumber'],
            sessionToken: $sessionToken
        );
    }

    protected function createQueryInvoiceDto(array $data): QueryInvoiceResponseDTO
    {
        $invoiceHeaders = array_map(
            fn (array $header) => $this->createInvoiceHeaderDto($header),
            $data['invoiceHeaderList']
        );

        return new QueryInvoiceResponseDTO(
            timestamp: Carbon::parse($data['timestamp']),
            referenceNumber: $data['referenceNumber'],
            invoiceHeaderList: $invoiceHeaders,
            numberOfElements: $data['numberOfElements'],
            pageOffset: $data['pageOffset'],
            pageSize: $data['pageSize']
        );
    }

    protected function createInvoiceHeaderDto(array $header): InvoiceHeaderDTO
    {
        $subjectBy = new SubjectByDTO(
            issuedByIdentifier: $header['subjectBy']['issuedByIdentifier'],
            issuedByName: $header['subjectBy']['issuedByName'],
            issuedToIdentifier: $header['subjectBy']['issuedToIdentifier'] ?? null,
            issuedToName: $header['subjectBy']['issuedToName'] ?? null
        );

        $subjectTo = new SubjectToDTO(
            issuedToIdentifier: $header['subjectTo']['issuedToIdentifier'],
            issuedToName: $header['subjectTo']['issuedToName']
        );

        return new InvoiceHeaderDTO(
            acquisitionTimestamp: Carbon::parse($header['acquisitionTimestamp']),
            currency: $header['currency'],
            faP17Annotation: $header['faP17Annotation'],
            gross: $header['gross'],
            invoiceReferenceNumber: $header['invoiceReferenceNumber'],
            invoiceType: $header['invoiceType'],
            invoicingDate: Carbon::parse($header['invoicingDate']),
            ksefReferenceNumber: $header['ksefReferenceNumber'],
            net: $header['net'],
            vat: $header['vat'],
            subjectBy: $subjectBy,
            subjectTo: $subjectTo,
            subjectToKList: $header['subjectToKList'] ?? null,
            subjectsAuthorizedList: $header['subjectsAuthorizedList'] ?? null,
            subjectsOtherList: $header['subjectsOtherList'] ?? null,
            schemaVersion: $header['schemaVersion'] ?? null
        );
    }

    protected function createTerminateSessionDto(array $data): TerminateSessionResponseDTO
    {
        return new TerminateSessionResponseDTO(
            timestamp: Carbon::parse($data['timestamp']),
            referenceNumber: $data['referenceNumber'],
            processingCode: $data['processingCode'],
            processingDescription: $data['processingDescription']
        );
    }

    protected function createSendInvoiceDto(array $data): SendInvoiceResponseDTO
    {
        return new SendInvoiceResponseDTO(
            timestamp: Carbon::parse($data['timestamp']),
            referenceNumber: $data['referenceNumber'],
            processingCode: $data['processingCode'],
            processingDescription: $data['processingDescription'],
            elementReferenceNumber: $data['elementReferenceNumber']
        );
    }

    protected function createStatusInvoiceDto(array $data): StatusInvoiceResponseDTO
    {
        $invoiceStatus = null;

        if (isset($data['invoiceStatus'])) {
            $statusData    = $data['invoiceStatus'];
            $invoiceStatus = new InvoiceStatusDTO(
                invoiceNumber: $statusData['invoiceNumber'],
                ksefReferenceNumber: $statusData['ksefReferenceNumber'],
                acquisitionTimestamp: Carbon::parse($statusData['acquisitionTimestamp'])
            );
        }

        return new StatusInvoiceResponseDTO(
            timestamp: Carbon::parse($data['timestamp']),
            referenceNumber: $data['referenceNumber'],
            processingCode: $data['processingCode'],
            processingDescription: $data['processingDescription'],
            elementReferenceNumber: $data['elementReferenceNumber'],
            invoiceStatus: $invoiceStatus
        );
    }
}
