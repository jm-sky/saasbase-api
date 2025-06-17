<?php

namespace App\Services\AzureDocumentIntelligence\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\AddressField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\CurrencyField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\DateField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\EmailField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\NumberField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\PhoneNumberField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\StringField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\TimeField;

/**
 * DTO for Invoice document from Azure Document Intelligence.
 */
final class InvoiceDocumentDTO extends BaseDataDTO
{
    public function __construct(
        // Basic Invoice Information
        public readonly ?StringField $invoiceId,
        public readonly ?StringField $customerName,
        public readonly ?StringField $customerTaxId,
        public readonly ?StringField $vendorName,
        public readonly ?StringField $vendorTaxId,
        public readonly ?StringField $purchaseOrder,
        public readonly ?StringField $invoiceType,
        public readonly ?NumberField $invoiceTypeConfidence,
        public readonly ?StringField $invoiceCategory,
        public readonly ?NumberField $invoiceCategoryConfidence,
        public readonly ?StringField $invoiceSubCategory,
        public readonly ?NumberField $invoiceSubCategoryConfidence,

        // Contact Information
        public readonly ?StringField $customerContactName,
        public readonly ?PhoneNumberField $customerContactPhone,
        public readonly ?EmailField $customerContactEmail,
        public readonly ?StringField $vendorContactName,
        public readonly ?PhoneNumberField $vendorContactPhone,
        public readonly ?EmailField $vendorContactEmail,

        // Addresses
        public readonly ?AddressField $customerAddress,
        public readonly ?StringField $customerAddressRecipient,
        public readonly ?AddressField $vendorAddress,
        public readonly ?StringField $vendorAddressRecipient,
        public readonly ?AddressField $serviceAddress,
        public readonly ?StringField $serviceAddressRecipient,
        public readonly ?AddressField $billingAddress,
        public readonly ?StringField $billingAddressRecipient,
        public readonly ?AddressField $shippingAddress,
        public readonly ?StringField $shippingAddressRecipient,
        public readonly ?AddressField $remittanceAddress,
        public readonly ?StringField $remittanceAddressRecipient,

        // Financial Information
        public readonly ?CurrencyField $amountDue,
        public readonly ?CurrencyField $invoiceTotal,
        public readonly ?CurrencyField $subTotal,
        public readonly ?CurrencyField $totalTax,
        public readonly ?StringField $paymentTerm,

        // Dates and Times
        public readonly ?DateField $dueDate,
        public readonly ?DateField $invoiceDate,
        public readonly ?TimeField $invoiceTime,
        public readonly ?DateField $serviceDate,

        // Collections
        public readonly array $items = [], // InvoiceDocumentItemDTO[]
        public readonly array $paymentDetails = [], // InvoiceDocumentPaymentDetailDTO[]

        // Document Confidence
        public readonly float $confidence = 1.0,
    ) {
    }

    /**
     * Map from Azure Document Intelligence response (fields array).
     */
    public static function fromArray(array $data): static
    {
        return new self(
            // Basic Invoice Information
            invoiceId: isset($data['invoiceId']) ? StringField::fromArray($data['invoiceId']) : null,
            customerName: isset($data['customerName']) ? StringField::fromArray($data['customerName']) : null,
            customerTaxId: isset($data['customerTaxId']) ? StringField::fromArray($data['customerTaxId']) : null,
            vendorName: isset($data['vendorName']) ? StringField::fromArray($data['vendorName']) : null,
            vendorTaxId: isset($data['vendorTaxId']) ? StringField::fromArray($data['vendorTaxId']) : null,
            purchaseOrder: isset($data['purchaseOrder']) ? StringField::fromArray($data['purchaseOrder']) : null,
            invoiceType: isset($data['invoiceType']) ? StringField::fromArray($data['invoiceType']) : null,
            invoiceTypeConfidence: isset($data['invoiceTypeConfidence']) ? NumberField::fromArray($data['invoiceTypeConfidence']) : null,
            invoiceCategory: isset($data['invoiceCategory']) ? StringField::fromArray($data['invoiceCategory']) : null,
            invoiceCategoryConfidence: isset($data['invoiceCategoryConfidence']) ? NumberField::fromArray($data['invoiceCategoryConfidence']) : null,
            invoiceSubCategory: isset($data['invoiceSubCategory']) ? StringField::fromArray($data['invoiceSubCategory']) : null,
            invoiceSubCategoryConfidence: isset($data['invoiceSubCategoryConfidence']) ? NumberField::fromArray($data['invoiceSubCategoryConfidence']) : null,

            // Contact Information
            customerContactName: isset($data['customerContactName']) ? StringField::fromArray($data['customerContactName']) : null,
            customerContactPhone: isset($data['customerContactPhone']) ? PhoneNumberField::fromArray($data['customerContactPhone']) : null,
            customerContactEmail: isset($data['customerContactEmail']) ? EmailField::fromArray($data['customerContactEmail']) : null,
            vendorContactName: isset($data['vendorContactName']) ? StringField::fromArray($data['vendorContactName']) : null,
            vendorContactPhone: isset($data['vendorContactPhone']) ? PhoneNumberField::fromArray($data['vendorContactPhone']) : null,
            vendorContactEmail: isset($data['vendorContactEmail']) ? EmailField::fromArray($data['vendorContactEmail']) : null,

            // Addresses
            customerAddress: isset($data['customerAddress']) ? AddressField::fromArray($data['customerAddress']) : null,
            customerAddressRecipient: isset($data['customerAddressRecipient']) ? StringField::fromArray($data['customerAddressRecipient']) : null,
            vendorAddress: isset($data['vendorAddress']) ? AddressField::fromArray($data['vendorAddress']) : null,
            vendorAddressRecipient: isset($data['vendorAddressRecipient']) ? StringField::fromArray($data['vendorAddressRecipient']) : null,
            serviceAddress: isset($data['serviceAddress']) ? AddressField::fromArray($data['serviceAddress']) : null,
            serviceAddressRecipient: isset($data['serviceAddressRecipient']) ? StringField::fromArray($data['serviceAddressRecipient']) : null,
            billingAddress: isset($data['billingAddress']) ? AddressField::fromArray($data['billingAddress']) : null,
            billingAddressRecipient: isset($data['billingAddressRecipient']) ? StringField::fromArray($data['billingAddressRecipient']) : null,
            shippingAddress: isset($data['shippingAddress']) ? AddressField::fromArray($data['shippingAddress']) : null,
            shippingAddressRecipient: isset($data['shippingAddressRecipient']) ? StringField::fromArray($data['shippingAddressRecipient']) : null,
            remittanceAddress: isset($data['remittanceAddress']) ? AddressField::fromArray($data['remittanceAddress']) : null,
            remittanceAddressRecipient: isset($data['remittanceAddressRecipient']) ? StringField::fromArray($data['remittanceAddressRecipient']) : null,

            // Financial Information
            amountDue: isset($data['amountDue']) ? CurrencyField::fromArray($data['amountDue']) : null,
            invoiceTotal: isset($data['invoiceTotal']) ? CurrencyField::fromArray($data['invoiceTotal']) : null,
            subTotal: isset($data['subTotal']) ? CurrencyField::fromArray($data['subTotal']) : null,
            totalTax: isset($data['totalTax']) ? CurrencyField::fromArray($data['totalTax']) : null,
            paymentTerm: isset($data['paymentTerm']) ? StringField::fromArray($data['paymentTerm']) : null,

            // Dates and Times
            dueDate: isset($data['dueDate']) ? DateField::fromArray($data['dueDate']) : null,
            invoiceDate: isset($data['invoiceDate']) ? DateField::fromArray($data['invoiceDate']) : null,
            invoiceTime: isset($data['invoiceTime']) ? TimeField::fromArray($data['invoiceTime']) : null,
            serviceDate: isset($data['serviceDate']) ? DateField::fromArray($data['serviceDate']) : null,

            // Collections
            items: array_map(
                fn ($item) => InvoiceDocumentItemDTO::fromArray($item['valueObject']),
                $data['items']['valueArray'] ?? []
            ),
            paymentDetails: array_map(
                fn ($item) => InvoiceDocumentPaymentDetailDTO::fromArray($item['valueObject']),
                $data['paymentDetails']['valueArray'] ?? []
            ),

            // Document Confidence
            confidence: (float) ($data['confidence'] ?? 1.0),
        );
    }

    /**
     * Map from Azure Document Intelligence response (fields array).
     */
    public static function fromAzureArray(array $data): static
    {
        $fields = $data['fields'] ?? [];

        return new self(
            // Basic Invoice Information
            invoiceId: isset($fields['InvoiceId']) ? StringField::fromAzureArray($fields['InvoiceId']) : null,
            customerName: isset($fields['CustomerName']) ? StringField::fromAzureArray($fields['CustomerName']) : null,
            customerTaxId: isset($fields['CustomerTaxId']) ? StringField::fromAzureArray($fields['CustomerTaxId']) : null,
            vendorName: isset($fields['VendorName']) ? StringField::fromAzureArray($fields['VendorName']) : null,
            vendorTaxId: isset($fields['VendorTaxId']) ? StringField::fromAzureArray($fields['VendorTaxId']) : null,
            purchaseOrder: isset($fields['PurchaseOrder']) ? StringField::fromAzureArray($fields['PurchaseOrder']) : null,
            invoiceType: isset($fields['InvoiceType']) ? StringField::fromAzureArray($fields['InvoiceType']) : null,
            invoiceTypeConfidence: isset($fields['InvoiceTypeConfidence']) ? NumberField::fromAzureArray($fields['InvoiceTypeConfidence']) : null,
            invoiceCategory: isset($fields['InvoiceCategory']) ? StringField::fromAzureArray($fields['InvoiceCategory']) : null,
            invoiceCategoryConfidence: isset($fields['InvoiceCategoryConfidence']) ? NumberField::fromAzureArray($fields['InvoiceCategoryConfidence']) : null,
            invoiceSubCategory: isset($fields['InvoiceSubCategory']) ? StringField::fromAzureArray($fields['InvoiceSubCategory']) : null,
            invoiceSubCategoryConfidence: isset($fields['InvoiceSubCategoryConfidence']) ? NumberField::fromAzureArray($fields['InvoiceSubCategoryConfidence']) : null,

            // Contact Information
            customerContactName: isset($fields['CustomerContactName']) ? StringField::fromAzureArray($fields['CustomerContactName']) : null,
            customerContactPhone: isset($fields['CustomerContactPhone']) ? PhoneNumberField::fromAzureArray($fields['CustomerContactPhone']) : null,
            customerContactEmail: isset($fields['CustomerContactEmail']) ? EmailField::fromAzureArray($fields['CustomerContactEmail']) : null,
            vendorContactName: isset($fields['VendorContactName']) ? StringField::fromAzureArray($fields['VendorContactName']) : null,
            vendorContactPhone: isset($fields['VendorContactPhone']) ? PhoneNumberField::fromAzureArray($fields['VendorContactPhone']) : null,
            vendorContactEmail: isset($fields['VendorContactEmail']) ? EmailField::fromAzureArray($fields['VendorContactEmail']) : null,

            // Addresses
            customerAddress: isset($fields['CustomerAddress']) ? AddressField::fromAzureArray($fields['CustomerAddress']) : null,
            customerAddressRecipient: isset($fields['CustomerAddressRecipient']) ? StringField::fromAzureArray($fields['CustomerAddressRecipient']) : null,
            vendorAddress: isset($fields['VendorAddress']) ? AddressField::fromAzureArray($fields['VendorAddress']) : null,
            vendorAddressRecipient: isset($fields['VendorAddressRecipient']) ? StringField::fromAzureArray($fields['VendorAddressRecipient']) : null,
            serviceAddress: isset($fields['ServiceAddress']) ? AddressField::fromAzureArray($fields['ServiceAddress']) : null,
            serviceAddressRecipient: isset($fields['ServiceAddressRecipient']) ? StringField::fromAzureArray($fields['ServiceAddressRecipient']) : null,
            billingAddress: isset($fields['BillingAddress']) ? AddressField::fromAzureArray($fields['BillingAddress']) : null,
            billingAddressRecipient: isset($fields['BillingAddressRecipient']) ? StringField::fromAzureArray($fields['BillingAddressRecipient']) : null,
            shippingAddress: isset($fields['ShippingAddress']) ? AddressField::fromAzureArray($fields['ShippingAddress']) : null,
            shippingAddressRecipient: isset($fields['ShippingAddressRecipient']) ? StringField::fromAzureArray($fields['ShippingAddressRecipient']) : null,
            remittanceAddress: isset($fields['RemittanceAddress']) ? AddressField::fromAzureArray($fields['RemittanceAddress']) : null,
            remittanceAddressRecipient: isset($fields['RemittanceAddressRecipient']) ? StringField::fromAzureArray($fields['RemittanceAddressRecipient']) : null,

            // Financial Information
            amountDue: isset($fields['AmountDue']) ? CurrencyField::fromAzureArray($fields['AmountDue']) : null,
            invoiceTotal: isset($fields['InvoiceTotal']) ? CurrencyField::fromAzureArray($fields['InvoiceTotal']) : null,
            subTotal: isset($fields['SubTotal']) ? CurrencyField::fromAzureArray($fields['SubTotal']) : null,
            totalTax: isset($fields['TotalTax']) ? CurrencyField::fromAzureArray($fields['TotalTax']) : null,
            paymentTerm: isset($fields['PaymentTerm']) ? StringField::fromAzureArray($fields['PaymentTerm']) : null,

            // Dates and Times
            dueDate: isset($fields['DueDate']) ? DateField::fromAzureArray($fields['DueDate']) : null,
            invoiceDate: isset($fields['InvoiceDate']) ? DateField::fromAzureArray($fields['InvoiceDate']) : null,
            invoiceTime: isset($fields['InvoiceTime']) ? TimeField::fromAzureArray($fields['InvoiceTime']) : null,
            serviceDate: isset($fields['ServiceDate']) ? DateField::fromAzureArray($fields['ServiceDate']) : null,

            // Collections
            items: array_map(
                fn ($item) => InvoiceDocumentItemDTO::fromAzureArray($item['valueObject']),
                $fields['Items']['valueArray'] ?? []
            ),
            paymentDetails: array_map(
                fn ($item) => InvoiceDocumentPaymentDetailDTO::fromAzureArray($item['valueObject']),
                $fields['PaymentDetails']['valueArray'] ?? []
            ),

            // Document Confidence
            confidence: (float) ($data['confidence'] ?? 1.0),
        );
    }

    public function toArray(): array
    {
        return [
            // Basic Invoice Information
            'invoiceId'                    => $this->invoiceId?->toArray(),
            'customerName'                 => $this->customerName?->toArray(),
            'customerTaxId'                => $this->customerTaxId?->toArray(),
            'vendorName'                   => $this->vendorName?->toArray(),
            'vendorTaxId'                  => $this->vendorTaxId?->toArray(),
            'purchaseOrder'                => $this->purchaseOrder?->toArray(),
            'invoiceType'                  => $this->invoiceType?->toArray(),
            'invoiceTypeConfidence'        => $this->invoiceTypeConfidence?->toArray(),
            'invoiceCategory'              => $this->invoiceCategory?->toArray(),
            'invoiceCategoryConfidence'    => $this->invoiceCategoryConfidence?->toArray(),
            'invoiceSubCategory'           => $this->invoiceSubCategory?->toArray(),
            'invoiceSubCategoryConfidence' => $this->invoiceSubCategoryConfidence?->toArray(),

            // Contact Information
            'customerContactName'  => $this->customerContactName?->toArray(),
            'customerContactPhone' => $this->customerContactPhone?->toArray(),
            'customerContactEmail' => $this->customerContactEmail?->toArray(),
            'vendorContactName'    => $this->vendorContactName?->toArray(),
            'vendorContactPhone'   => $this->vendorContactPhone?->toArray(),
            'vendorContactEmail'   => $this->vendorContactEmail?->toArray(),

            // Addresses
            'customerAddress'            => $this->customerAddress?->toArray(),
            'customerAddressRecipient'   => $this->customerAddressRecipient?->toArray(),
            'vendorAddress'              => $this->vendorAddress?->toArray(),
            'vendorAddressRecipient'     => $this->vendorAddressRecipient?->toArray(),
            'serviceAddress'             => $this->serviceAddress?->toArray(),
            'serviceAddressRecipient'    => $this->serviceAddressRecipient?->toArray(),
            'billingAddress'             => $this->billingAddress?->toArray(),
            'billingAddressRecipient'    => $this->billingAddressRecipient?->toArray(),
            'shippingAddress'            => $this->shippingAddress?->toArray(),
            'shippingAddressRecipient'   => $this->shippingAddressRecipient?->toArray(),
            'remittanceAddress'          => $this->remittanceAddress?->toArray(),
            'remittanceAddressRecipient' => $this->remittanceAddressRecipient?->toArray(),

            // Financial Information
            'amountDue'    => $this->amountDue?->toArray(),
            'invoiceTotal' => $this->invoiceTotal?->toArray(),
            'subTotal'     => $this->subTotal?->toArray(),
            'totalTax'     => $this->totalTax?->toArray(),
            'paymentTerm'  => $this->paymentTerm,

            // Dates and Times
            'dueDate'     => $this->dueDate?->toArray(),
            'invoiceDate' => $this->invoiceDate?->toArray(),
            'invoiceTime' => $this->invoiceTime?->toArray(),
            'serviceDate' => $this->serviceDate?->toArray(),

            // Collections
            'items'          => array_map(fn ($item) => $item->toArray(), $this->items),
            'paymentDetails' => array_map(fn ($pd) => $pd->toArray(), $this->paymentDetails),

            // Document Confidence
            'confidence' => $this->confidence,
        ];
    }
}
