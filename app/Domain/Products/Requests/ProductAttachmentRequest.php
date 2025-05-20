<?php

namespace App\Domain\Products\Requests;

use App\Http\Requests\BaseFormRequest;

class ProductAttachmentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true; // Add authorization logic if needed
    }

    public function rules(): array
    {
        $maxSize = config('domains.products.attachments.max_size', 10240); // in kilobytes

        return [
            'file' => ['required', 'file', 'max:' . $maxSize],
        ];
    }
}
