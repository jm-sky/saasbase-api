<?php

namespace App\Domain\EDoreczenia\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edoreczenia.view');
    }

    public function rules(): array
    {
        return [
            'is_read' => ['sometimes', 'boolean'],
        ];
    }
}
