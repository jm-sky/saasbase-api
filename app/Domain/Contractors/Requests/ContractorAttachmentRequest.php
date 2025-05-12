<?php

namespace App\Domain\Contractors\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractorAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add authorization logic if needed
    }

    public function rules(): array
    {
        $maxSize = config('domains.contractors.attachments.max_size', 10240); // in kilobytes

        return [
            'file' => ['required', 'file', 'max:' . $maxSize],
        ];
    }
}
