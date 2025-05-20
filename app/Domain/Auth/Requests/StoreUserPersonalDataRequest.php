<?php

namespace App\Domain\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserPersonalDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gender' => ['required', 'in:male,female,prefer_not_to_say'],
            'pesel'  => ['required', 'string', 'max:11'],
        ];
    }
}
