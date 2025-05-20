<?php

namespace App\Domain\Auth\Requests;

use App\Http\Requests\BaseFormRequest;

class UploadProfileImageRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'max:2048'],
        ];
    }
}
