<?php

namespace App\Domain\Auth\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateUserProfileRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'firstName'   => ['required', 'string', 'min:2', 'max:50'],
            // 'lastName'    => ['required', 'string', 'min:2', 'max:50'],
            'bio'                   => ['nullable', 'string', 'min:2', 'max:255'],
            'location'              => ['nullable', 'string', 'min:2', 'max:255'],
            'birthDate'             => ['nullable', 'date'],
            'position'              => ['nullable', 'string', 'min:2', 'max:255'],
            'website'               => ['nullable', 'string', 'min:2', 'max:255'],
            'socialLinks'           => ['nullable', 'array'],
            'socialLinks.facebook'  => ['nullable', 'string', 'min:2', 'max:255'],
            'socialLinks.instagram' => ['nullable', 'string', 'min:2', 'max:255'],
            'socialLinks.twitter'   => ['nullable', 'string', 'min:2', 'max:255'],
            'socialLinks.linkedin'  => ['nullable', 'string', 'min:2', 'max:255'],
            'socialLinks.youtube'   => ['nullable', 'string', 'min:2', 'max:255'],
        ];
    }
}
