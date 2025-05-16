<?php

namespace App\Domain\Products\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductLogoUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add authorization logic if needed
    }

    public function rules(): array
    {
        $maxSize      = config('domains.products.logo.max_size', 2048); // in kilobytes
        $allowedMimes = config('domains.products.logo.allowed_mimes', ['image/jpeg', 'image/png', 'image/webp']);

        return [
            'image' => [
                'required',
                'image',
                'mimes:' . implode(',', array_map(fn ($mime) => explode('/', $mime)[1], $allowedMimes)),
                'max:' . $maxSize,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'The logo image is required.',
            'image.image'    => 'The file must be an image.',
            'image.mimes'    => 'The logo must be a file of type: :values.',
            'image.max'      => 'The logo may not be greater than :max kilobytes.',
        ];
    }
}
