<?php

namespace App\Domain\Contractors\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractorCommentRequest extends FormRequest
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
