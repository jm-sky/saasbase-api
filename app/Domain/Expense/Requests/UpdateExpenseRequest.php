<?php

namespace App\Domain\Expense\Requests;

use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Financial\Enums\PaymentStatus;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateExpenseRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Keys here must be camelCase: rules() and $this->has()/input() operate
     * on the raw request as sent by the client — BaseFormRequest::validated()
     * only snake_cases the OUTPUT afterwards. An earlier snake_case version
     * of this file (matching a bug found and fixed in UpdateInvoiceRequest)
     * meant issueDate/totalNet/totalTax/totalGross/exchangeRate were never
     * actually validated or written by PATCH/PUT — a silent no-op that
     * looked like a successful 200 response.
     */
    public function rules(): array
    {
        return [
            'type'         => ['sometimes', new Enum(InvoiceType::class)],
            'issueDate'    => ['sometimes', 'date'],
            'status'       => ['sometimes', 'string', new Enum(InvoiceStatus::class)],
            'number'       => ['sometimes', 'string'],
            'totalNet'     => ['sometimes', 'numeric'],
            'totalTax'     => ['sometimes', 'numeric'],
            'totalGross'   => ['sometimes', 'numeric'],
            'currency'     => ['sometimes', 'string', 'size:3'],
            'exchangeRate' => ['sometimes', 'numeric'],
            'seller'      => ['sometimes', 'array'],
            'buyer'       => ['sometimes', 'array'],
            'body'        => ['sometimes', 'array'],
            'options'     => ['sometimes', 'array'],

            // Expense.payment uses the same InvoicePaymentCast/DTO as
            // Invoice — an unvalidated shape here doesn't fail this
            // request, it fails on every SUBSEQUENT read of the expense
            // (PaymentStatus::from()/PaymentMethodDTO::fromArray() have no
            // null/invalid guards), making it permanently unreadable via
            // the API. Same fix as UpdateInvoiceRequest.
            'payment'                     => ['sometimes', 'array'],
            'payment.status'              => ['required_with:payment', new Enum(PaymentStatus::class)],
            'payment.dueDate'             => ['nullable', 'date'],
            'payment.paidDate'            => ['nullable', 'date'],
            'payment.paidAmount'          => ['nullable', 'numeric', 'min:0'],
            'payment.method'              => ['required_with:payment', 'array'],
            'payment.method.id'           => ['required_with:payment.method', 'string', 'exists:payment_methods,id'],
            'payment.method.name'         => ['required_with:payment.method', 'string', 'max:255'],
            'payment.method.paymentDays'  => ['nullable', 'integer', 'min:0'],
            'payment.reference'           => ['nullable', 'string', 'max:255'],
            'payment.terms'               => ['nullable', 'string', 'max:500'],
            'payment.notes'               => ['nullable', 'string', 'max:1000'],
            'payment.bankAccount'         => ['nullable', 'array'],
            'payment.bankAccount.name'    => ['nullable', 'string', 'max:255'],
            'payment.bankAccount.iban'    => ['nullable', 'string', 'max:34'],
            'payment.bankAccount.swift'   => ['nullable', 'string', 'max:11'],
            'payment.bankAccount.address' => ['nullable', 'string', 'max:500'],
        ];
    }
}
