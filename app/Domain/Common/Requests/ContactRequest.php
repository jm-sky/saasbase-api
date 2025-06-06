<?php

namespace App\Domain\Common\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'            => ['nullable', 'string', 'max:255'],
            'last_name'             => ['nullable', 'string', 'max:255'],
            'position'              => ['nullable', 'string', 'max:255'],
            'email'                 => ['nullable', 'email', 'max:255'],
            'phone_number'          => ['nullable', 'string', 'max:255'],
            'emails'                => ['nullable', 'array'],
            'emails.*.label'        => ['required_with:emails.*.email', 'string', 'max:255'],
            'emails.*.email'        => ['required_with:emails.*.label', 'email', 'max:255'],
            'phone_numbers'         => ['nullable', 'array'],
            'phone_numbers.*.label' => ['required_with:phone_numbers.*.phone', 'string', 'max:255'],
            'phone_numbers.*.phone' => ['required_with:phone_numbers.*.label', 'string', 'max:255'],
            'notes'                 => ['nullable', 'string'],
            'user_id'               => ['nullable', 'ulid', 'exists:users,id'],
            'contactable_id'        => ['required', 'ulid'],
            'contactable_type'      => ['required', 'string'],
        ];
    }
}
