<?php

namespace App\Domain\Ai\Requests;

use App\Http\Requests\BaseFormRequest;

class AiChatRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return null !== $this->user();
    }

    public function rules(): array
    {
        return [
            'message'   => ['required', 'string'],
            'history'   => ['nullable', 'array'],
            'threadId'  => ['nullable', 'string'],
        ];
    }
}
