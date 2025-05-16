<?php

namespace App\Domain\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadProfileImageRequest extends FormRequest
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
