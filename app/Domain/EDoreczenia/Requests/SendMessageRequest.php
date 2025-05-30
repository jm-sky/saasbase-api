<?php

namespace App\Domain\EDoreczenia\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edoreczenia.send');
    }

    public function rules(): array
    {
        return [
            'subject'                 => ['required', 'string', 'max:255'],
            'content'                 => ['required', 'string'],
            'recipients'              => ['required', 'array', 'min:1'],
            'recipients.*.email'      => ['required', 'email'],
            'recipients.*.name'       => ['required', 'string', 'max:255'],
            'recipients.*.identifier' => ['required', 'string', 'max:255'],
            'attachments'             => ['sometimes', 'array', 'max:' . config('edoreczenia.messages.max_attachments')],
            'attachments.*'           => [
                'file',
                'max:' . (config('edoreczenia.messages.max_attachment_size') / 1024), // Convert to KB
            ],
            'ref_to_message_id' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
