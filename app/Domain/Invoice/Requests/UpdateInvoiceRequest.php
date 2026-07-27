<?php

namespace App\Domain\Invoice\Requests;

use App\Domain\Auth\Models\User;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Financial\Enums\PaymentStatus;
use App\Domain\Invoice\Models\Invoice;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class UpdateInvoiceRequest extends BaseFormRequest
{
    /**
     * Fields that change the financial substance of the document. Locked
     * once the invoice has reached a final state (completed/cancelled).
     *
     * These must be camelCase: rules() and $this->has()/input() operate on
     * the raw request as sent by the client (BaseFormRequest::validated()
     * only snake_cases the OUTPUT afterwards) — using snake_case here, as
     * an earlier version of this file did, silently never matches the
     * request body and makes every check in this class a no-op.
     */
    private const FINANCIAL_FIELDS = ['number', 'totalNet', 'totalTax', 'totalGross', 'currency', 'exchangeRate', 'body'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        /** @var ?Invoice $invoice */
        $invoice = $this->route('invoice');

        return [
            'type' => ['sometimes', new Enum(InvoiceType::class)],
            'issueDate' => ['sometimes', 'date'],
            'status' => ['sometimes', 'string', new Enum(InvoiceStatus::class)],
            'number' => [
                'sometimes',
                'string',
                Rule::unique('invoices', 'number')
                    ->where('tenant_id', $user->getTenantId())
                    ->ignore($invoice?->id),
            ],
            'numberingTemplateId' => ['sometimes', 'string', 'exists:numbering_templates,id'],
            'totalNet' => ['sometimes', 'numeric'],
            'totalTax' => ['sometimes', 'numeric'],
            'totalGross' => ['sometimes', 'numeric'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'exchangeRate' => ['sometimes', 'numeric'],
            'seller' => ['sometimes', 'array'],
            'buyer' => ['sometimes', 'array'],
            'body' => ['sometimes', 'array'],
            'options' => ['sometimes', 'array'],

            // InvoicePaymentDTO::fromArray() does PaymentStatus::from() (not
            // tryFrom) and PaymentMethodDTO::fromArray($data['method']) with
            // no null guard — an unvalidated 'payment' shape here doesn't
            // fail this request, it fails on every SUBSEQUENT read of the
            // invoice (ValueError/TypeError from the cast), making it
            // permanently unreadable via the API until the row is fixed by
            // hand. Mirrors StoreInvoiceRequest's payment.* rules, but
            // required_with instead of required since payment as a whole is
            // still optional on update.
            'payment' => ['sometimes', 'array'],
            'payment.status' => ['required_with:payment', new Enum(PaymentStatus::class)],
            'payment.dueDate' => ['nullable', 'date'],
            'payment.paidDate' => ['nullable', 'date'],
            'payment.paidAmount' => ['nullable', 'numeric', 'min:0'],
            'payment.method' => ['required_with:payment', 'array'],
            'payment.method.id' => ['required_with:payment.method', 'string', 'exists:payment_methods,id'],
            'payment.method.name' => ['required_with:payment.method', 'string', 'max:255'],
            'payment.method.paymentDays' => ['nullable', 'integer', 'min:0'],
            'payment.reference' => ['nullable', 'string', 'max:255'],
            'payment.terms' => ['nullable', 'string', 'max:500'],
            'payment.notes' => ['nullable', 'string', 'max:1000'],
            'payment.bankAccount' => ['nullable', 'array'],
            'payment.bankAccount.name' => ['nullable', 'string', 'max:255'],
            'payment.bankAccount.iban' => ['nullable', 'string', 'max:34'],
            'payment.bankAccount.swift' => ['nullable', 'string', 'max:11'],
            'payment.bankAccount.address' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var ?Invoice $invoice */
            $invoice = $this->route('invoice');

            if (! $invoice) {
                return;
            }

            $this->validateStatusTransition($validator, $invoice);
            $this->validateFinancialFieldsNotLocked($validator, $invoice);
            $this->validateFinancialSum($validator);
        });
    }

    private function validateStatusTransition(Validator $validator, Invoice $invoice): void
    {
        if (! $this->has('status')) {
            return;
        }

        $newStatus = InvoiceStatus::tryFrom((string) $this->input('status'));

        if (! $newStatus || $newStatus === $invoice->status) {
            return;
        }

        if (! $invoice->status->canTransitionTo($newStatus)) {
            $validator->errors()->add('status', "Cannot transition invoice from {$invoice->status->value} to {$newStatus->value}.");
        }
    }

    private function validateFinancialFieldsNotLocked(Validator $validator, Invoice $invoice): void
    {
        if (! $invoice->status->isCompleted()) {
            return;
        }

        foreach (self::FINANCIAL_FIELDS as $field) {
            if ($this->has($field)) {
                $validator->errors()->add($field, 'This invoice is completed/cancelled and its financial details can no longer be edited.');
            }
        }
    }

    private function validateFinancialSum(Validator $validator): void
    {
        $net = $this->input('totalNet');
        $tax = $this->input('totalTax');
        $gross = $this->input('totalGross');

        if ($net === null || $tax === null || $gross === null) {
            return;
        }

        if (round(($net + $tax) * 100) !== round($gross * 100)) {
            $validator->errors()->add('totalGross', 'totalNet + totalTax must equal totalGross.');
        }
    }
}
