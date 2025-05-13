<?php

namespace App\Domain\Ai\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiChatRequest extends FormRequest
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
