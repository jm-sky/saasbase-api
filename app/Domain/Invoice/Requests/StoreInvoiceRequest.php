<?php

namespace App\Domain\Invoice\Requests;

use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Financial\Enums\AllocationStatus;
use App\Domain\Financial\Enums\ApprovalStatus;
use App\Domain\Financial\Enums\DeliveryStatus;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Financial\Enums\PaymentMethod;
use App\Domain\Financial\Enums\PaymentStatus;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreInvoiceRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'                     => ['required', new Enum(InvoiceType::class)],
            'issueDate'                => ['required', 'date'],
            // Backward compatibility
            'status'                   => ['sometimes', 'string'],
            // New status structure
            'statusInfo'               => ['sometimes', 'array'],
            'statusInfo.general'       => ['sometimes', new Enum(InvoiceStatus::class)],
            'statusInfo.ocr'           => ['sometimes', 'nullable', new Enum(OcrRequestStatus::class)],
            'statusInfo.allocation'    => ['sometimes', 'nullable', new Enum(AllocationStatus::class)],
            'statusInfo.approval'      => ['sometimes', 'nullable', new Enum(ApprovalStatus::class)],
            'statusInfo.delivery'      => ['sometimes', 'nullable', new Enum(DeliveryStatus::class)],
            'statusInfo.payment'       => ['sometimes', 'nullable', new Enum(PaymentStatus::class)],
            // Other fields
            'number'                   => ['required', 'string', 'max:255'],
            'numberingTemplateId'      => ['required', 'string', 'exists:numbering_templates,id'],
            'totalNet'                 => ['required', 'numeric', 'min:0'],
            'totalTax'                 => ['required', 'numeric', 'min:0'],
            'totalGross'               => ['required', 'numeric', 'min:0'],
            'currency'                 => ['required', 'string', 'size:3'],
            'exchangeRate'             => ['required', 'numeric', 'min:0'],

            // Seller validation
            'seller'                   => ['required', 'array'],
            'seller.name'              => ['required', 'string', 'max:255'],
            'seller.address'           => ['nullable', 'string', 'max:500'],
            'seller.country'           => ['nullable', 'string', 'max:2'],
            'seller.taxId'             => ['nullable', 'string', 'max:50'],
            'seller.iban'              => ['nullable', 'string', 'max:34'],
            'seller.contractorId'      => ['nullable', 'string', 'exists:contractors,id'],
            'seller.contractorType'    => ['nullable', 'string', 'max:50'],
            'seller.email'             => ['nullable', 'email', 'max:255'],

            // Buyer validation
            'buyer'                    => ['required', 'array'],
            'buyer.name'               => ['required', 'string', 'max:255'],
            'buyer.address'            => ['nullable', 'string', 'max:500'],
            'buyer.country'            => ['nullable', 'string', 'max:2'],
            'buyer.taxId'              => ['nullable', 'string', 'max:50'],
            'buyer.iban'               => ['nullable', 'string', 'max:34'],
            'buyer.contractorId'       => ['nullable', 'string', 'exists:contractors,id'],
            'buyer.contractorType'     => ['nullable', 'string', 'max:50'],
            'buyer.email'              => ['nullable', 'email', 'max:255'],

            // Body validation
            'body'                               => ['required', 'array'],
            'body.lines'                         => ['required', 'array', 'min:1'],
            'body.lines.*.id'                    => ['required', 'string', 'max:255'],
            'body.lines.*.description'           => ['nullable', 'string', 'max:1000'],
            'body.lines.*.quantity'              => ['required', 'numeric', 'min:0'],
            'body.lines.*.unitPrice'             => ['required', 'numeric', 'min:0'],
            'body.lines.*.vatRate'               => ['required', 'array'],
            'body.lines.*.vatRate.rate'          => ['required', 'numeric', 'min:0', 'max:100'],
            'body.lines.*.vatRate.category'      => ['nullable', 'string', 'max:50'],
            'body.lines.*.totalNet'              => ['required', 'numeric', 'min:0'],
            'body.lines.*.totalVat'              => ['required', 'numeric', 'min:0'],
            'body.lines.*.totalGross'            => ['required', 'numeric', 'min:0'],
            'body.lines.*.productId'             => ['nullable', 'string', 'exists:products,id'],
            'body.lines.*.gtuCodes'              => ['nullable', 'array'],
            'body.lines.*.gtuCodes.*'            => ['string', 'max:10'],
            'body.vatSummary'                    => ['required', 'array'],
            'body.vatSummary.*.vatRate'          => ['required', 'array'],
            'body.vatSummary.*.vatRate.rate'     => ['required', 'numeric', 'min:0', 'max:100'],
            'body.vatSummary.*.vatRate.category' => ['nullable', 'string', 'max:50'],
            'body.vatSummary.*.net'              => ['required', 'numeric', 'min:0'],
            'body.vatSummary.*.vat'              => ['required', 'numeric', 'min:0'],
            'body.vatSummary.*.gross'            => ['required', 'numeric', 'min:0'],
            'body.exchange'                      => ['required', 'array'],
            'body.exchange.currency'             => ['required', 'string', 'size:3'],
            'body.exchange.exchangeRate'         => ['nullable', 'numeric', 'min:0'],
            'body.exchange.date'                 => ['nullable', 'date'],
            'body.description'                   => ['nullable', 'string', 'max:2000'],

            // Payment validation
            'payment'                     => ['required', 'array'],
            'payment.status'              => ['required', new Enum(PaymentStatus::class)],
            'payment.dueDate'             => ['nullable', 'date'],
            'payment.paidDate'            => ['nullable', 'date'],
            'payment.paidAmount'          => ['nullable', 'numeric', 'min:0'],
            'payment.method'              => ['required', new Enum(PaymentMethod::class)],
            'payment.reference'           => ['nullable', 'string', 'max:255'],
            'payment.terms'               => ['nullable', 'string', 'max:500'],
            'payment.notes'               => ['nullable', 'string', 'max:1000'],
            'payment.bankAccount'         => ['nullable', 'array'],
            'payment.bankAccount.name'    => ['nullable', 'string', 'max:255'],
            'payment.bankAccount.iban'    => ['nullable', 'string', 'max:34'],
            'payment.bankAccount.swift'   => ['nullable', 'string', 'max:11'],
            'payment.bankAccount.address' => ['nullable', 'string', 'max:500'],

            // Options validation
            'options'                  => ['required', 'array'],
            'options.language'         => ['nullable', 'string', 'max:5'],
            'options.template'         => ['nullable', 'string', 'max:255'],
            'options.sendEmail'        => ['required', 'boolean'],
            'options.emailTo'          => ['required', 'array'],
            'options.emailTo.*'        => ['email', 'max:255'],
        ];
    }
}
