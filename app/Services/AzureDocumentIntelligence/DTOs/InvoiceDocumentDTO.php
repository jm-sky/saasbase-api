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
        $fields = $data['fields'] ?? [];

        // dd($fields['PaymentDetails']);

        return new self(
            // Basic Invoice Information
            invoiceId: isset($fields['InvoiceId']) ? StringField::fromArray($fields['InvoiceId']) : null,
            customerName: isset($fields['CustomerName']) ? StringField::fromArray($fields['CustomerName']) : null,
            customerTaxId: isset($fields['CustomerTaxId']) ? StringField::fromArray($fields['CustomerTaxId']) : null,
            vendorName: isset($fields['VendorName']) ? StringField::fromArray($fields['VendorName']) : null,
            vendorTaxId: isset($fields['VendorTaxId']) ? StringField::fromArray($fields['VendorTaxId']) : null,
            purchaseOrder: isset($fields['PurchaseOrder']) ? StringField::fromArray($fields['PurchaseOrder']) : null,
            invoiceType: isset($fields['InvoiceType']) ? StringField::fromArray($fields['InvoiceType']) : null,
            invoiceTypeConfidence: isset($fields['InvoiceTypeConfidence']) ? NumberField::fromArray($fields['InvoiceTypeConfidence']) : null,
            invoiceCategory: isset($fields['InvoiceCategory']) ? StringField::fromArray($fields['InvoiceCategory']) : null,
            invoiceCategoryConfidence: isset($fields['InvoiceCategoryConfidence']) ? NumberField::fromArray($fields['InvoiceCategoryConfidence']) : null,
            invoiceSubCategory: isset($fields['InvoiceSubCategory']) ? StringField::fromArray($fields['InvoiceSubCategory']) : null,
            invoiceSubCategoryConfidence: isset($fields['InvoiceSubCategoryConfidence']) ? NumberField::fromArray($fields['InvoiceSubCategoryConfidence']) : null,

            // Contact Information
            customerContactName: isset($fields['CustomerContactName']) ? StringField::fromArray($fields['CustomerContactName']) : null,
            customerContactPhone: isset($fields['CustomerContactPhone']) ? PhoneNumberField::fromArray($fields['CustomerContactPhone']) : null,
            customerContactEmail: isset($fields['CustomerContactEmail']) ? EmailField::fromArray($fields['CustomerContactEmail']) : null,
            vendorContactName: isset($fields['VendorContactName']) ? StringField::fromArray($fields['VendorContactName']) : null,
            vendorContactPhone: isset($fields['VendorContactPhone']) ? PhoneNumberField::fromArray($fields['VendorContactPhone']) : null,
            vendorContactEmail: isset($fields['VendorContactEmail']) ? EmailField::fromArray($fields['VendorContactEmail']) : null,

            // Addresses
            customerAddress: isset($fields['CustomerAddress']) ? AddressField::fromArray($fields['CustomerAddress']) : null,
            customerAddressRecipient: isset($fields['CustomerAddressRecipient']) ? StringField::fromArray($fields['CustomerAddressRecipient']) : null,
            vendorAddress: isset($fields['VendorAddress']) ? AddressField::fromArray($fields['VendorAddress']) : null,
            vendorAddressRecipient: isset($fields['VendorAddressRecipient']) ? StringField::fromArray($fields['VendorAddressRecipient']) : null,
            serviceAddress: isset($fields['ServiceAddress']) ? AddressField::fromArray($fields['ServiceAddress']) : null,
            serviceAddressRecipient: isset($fields['ServiceAddressRecipient']) ? StringField::fromArray($fields['ServiceAddressRecipient']) : null,
            billingAddress: isset($fields['BillingAddress']) ? AddressField::fromArray($fields['BillingAddress']) : null,
            billingAddressRecipient: isset($fields['BillingAddressRecipient']) ? StringField::fromArray($fields['BillingAddressRecipient']) : null,
            shippingAddress: isset($fields['ShippingAddress']) ? AddressField::fromArray($fields['ShippingAddress']) : null,
            shippingAddressRecipient: isset($fields['ShippingAddressRecipient']) ? StringField::fromArray($fields['ShippingAddressRecipient']) : null,
            remittanceAddress: isset($fields['RemittanceAddress']) ? AddressField::fromArray($fields['RemittanceAddress']) : null,
            remittanceAddressRecipient: isset($fields['RemittanceAddressRecipient']) ? StringField::fromArray($fields['RemittanceAddressRecipient']) : null,

            // Financial Information
            amountDue: isset($fields['AmountDue']) ? CurrencyField::fromArray($fields['AmountDue']) : null,
            invoiceTotal: isset($fields['InvoiceTotal']) ? CurrencyField::fromArray($fields['InvoiceTotal']) : null,
            subTotal: isset($fields['SubTotal']) ? CurrencyField::fromArray($fields['SubTotal']) : null,
            totalTax: isset($fields['TotalTax']) ? CurrencyField::fromArray($fields['TotalTax']) : null,
            paymentTerm: isset($fields['PaymentTerm']) ? StringField::fromArray($fields['PaymentTerm']) : null,

            // Dates and Times
            dueDate: isset($fields['DueDate']) ? DateField::fromArray($fields['DueDate']) : null,
            invoiceDate: isset($fields['InvoiceDate']) ? DateField::fromArray($fields['InvoiceDate']) : null,
            invoiceTime: isset($fields['InvoiceTime']) ? TimeField::fromArray($fields['InvoiceTime']) : null,
            serviceDate: isset($fields['ServiceDate']) ? DateField::fromArray($fields['ServiceDate']) : null,

            // Collections
            items: array_map(
                fn ($item) => InvoiceDocumentItemDTO::fromArray($item['valueObject']),
                $fields['Items']['valueArray'] ?? []
            ),
            paymentDetails: array_map(
                fn ($item) => InvoiceDocumentPaymentDetailDTO::fromArray($item['valueObject']),
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
            'invoiceId'                    => $this->invoiceId,
            'customerName'                 => $this->customerName,
            'customerTaxId'                => $this->customerTaxId,
            'vendorName'                   => $this->vendorName,
            'vendorTaxId'                  => $this->vendorTaxId,
            'purchaseOrder'                => $this->purchaseOrder,
            'invoiceType'                  => $this->invoiceType,
            'invoiceTypeConfidence'        => $this->invoiceTypeConfidence,
            'invoiceCategory'              => $this->invoiceCategory,
            'invoiceCategoryConfidence'    => $this->invoiceCategoryConfidence,
            'invoiceSubCategory'           => $this->invoiceSubCategory,
            'invoiceSubCategoryConfidence' => $this->invoiceSubCategoryConfidence,

            // Contact Information
            'customerContactName'  => $this->customerContactName,
            'customerContactPhone' => $this->customerContactPhone,
            'customerContactEmail' => $this->customerContactEmail,
            'vendorContactName'    => $this->vendorContactName,
            'vendorContactPhone'   => $this->vendorContactPhone,
            'vendorContactEmail'   => $this->vendorContactEmail,

            // Addresses
            'customerAddress'            => $this->customerAddress,
            'customerAddressRecipient'   => $this->customerAddressRecipient,
            'vendorAddress'              => $this->vendorAddress,
            'vendorAddressRecipient'     => $this->vendorAddressRecipient,
            'serviceAddress'             => $this->serviceAddress,
            'serviceAddressRecipient'    => $this->serviceAddressRecipient,
            'billingAddress'             => $this->billingAddress,
            'billingAddressRecipient'    => $this->billingAddressRecipient,
            'shippingAddress'            => $this->shippingAddress,
            'shippingAddressRecipient'   => $this->shippingAddressRecipient,
            'remittanceAddress'          => $this->remittanceAddress,
            'remittanceAddressRecipient' => $this->remittanceAddressRecipient,

            // Financial Information
            'amountDue'    => $this->amountDue?->toArray(),
            'invoiceTotal' => $this->invoiceTotal?->toArray(),
            'subTotal'     => $this->subTotal?->toArray(),
            'totalTax'     => $this->totalTax?->toArray(),
            'paymentTerm'  => $this->paymentTerm,

            // Dates and Times
            'dueDate'     => $this->dueDate,
            'invoiceDate' => $this->invoiceDate,
            'invoiceTime' => $this->invoiceTime,
            'serviceDate' => $this->serviceDate,

            // Collections
            'items'          => array_map(fn ($item) => $item->toArray(), $this->items),
            'paymentDetails' => array_map(fn ($pd) => $pd->toArray(), $this->paymentDetails),

            // Document Confidence
            'confidence' => $this->confidence,
        ];
    }
}
