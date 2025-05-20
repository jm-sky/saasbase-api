<?php

namespace App\Domain\Users\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateProfileRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatarUrl'     => ['nullable', 'string', 'url'],
            'bio'           => ['nullable', 'string', 'max:1000'],
            'location'      => ['nullable', 'string', 'max:255'],
            'birthDate'     => ['nullable', 'date'],
            'position'      => ['nullable', 'string', 'max:255'],
            'website'       => ['nullable', 'string', 'url', 'max:255'],
            'socialLinks'   => ['nullable', 'array'],
            'socialLinks.*' => ['string', 'url'],
        ];
    }
}
