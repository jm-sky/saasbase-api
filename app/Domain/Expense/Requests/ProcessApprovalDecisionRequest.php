<?php

namespace App\Domain\Expense\Requests;

use App\Domain\Approval\Enums\ApprovalDecision;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class ProcessApprovalDecisionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', new Enum(ApprovalDecision::class)],
            'reason'   => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'Approval decision is required.',
            'decision.enum'     => 'Invalid approval decision. Must be approved or rejected.',
            'notes.max'         => 'Notes cannot exceed 1000 characters.',
        ];
    }
}
