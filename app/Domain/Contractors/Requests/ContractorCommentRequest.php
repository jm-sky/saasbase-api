<?php

namespace App\Domain\Contractors\Requests;

use App\Http\Requests\BaseFormRequest;

class ContractorCommentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'content' => $validated['content'],
        ];
    }
}
