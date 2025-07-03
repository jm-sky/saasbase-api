<?php

namespace App\Domain\Expense\Requests;

use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Financial\Enums\AllocationStatus;
use App\Domain\Financial\Enums\ApprovalStatus;
use App\Domain\Financial\Enums\DeliveryStatus;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Financial\Enums\PaymentStatus;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreExpenseRequest extends BaseFormRequest
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
            'number'                   => ['required', 'string'],
            'totalNet'                 => ['required', 'numeric'],
            'totalTax'                 => ['required', 'numeric'],
            'totalGross'               => ['required', 'numeric'],
            'currency'                 => ['required', 'string', 'size:3'],
            'exchangeRate'             => ['required', 'numeric'],
            'seller'                   => ['required', 'array'],
            'buyer'                    => ['required', 'array'],
            'body'                     => ['required', 'array'],
            'payment'                  => ['required', 'array'],
            'options'                  => ['required', 'array'],
        ];
    }
}
