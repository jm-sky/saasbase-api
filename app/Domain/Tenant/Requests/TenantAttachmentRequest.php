<?php

namespace App\Domain\Tenant\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add authorization logic if needed
    }

    public function rules(): array
    {
        $maxSize = config('domains.tenants.attachments.max_size', 10240); // in kilobytes

        return [
            'file' => ['required', 'file', 'max:' . $maxSize],
        ];
    }
}
