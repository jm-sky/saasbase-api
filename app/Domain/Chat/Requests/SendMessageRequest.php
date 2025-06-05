<?php

namespace App\Domain\Chat\Requests;

use App\Http\Requests\BaseFormRequest;

class SendMessageRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'  => ['required', 'string'],
            'parentId' => ['nullable', 'ulid', 'exists:chat_messages,id'],
        ];
    }
}
