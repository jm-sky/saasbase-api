<?php

namespace App\Domain\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add authorization logic if needed
    }

    public function rules(): array
    {
        $maxSize = config('domains.projects.attachments.max_size', 10240); // in kilobytes

        return [
            'file' => ['required', 'file', 'max:' . $maxSize],
        ];
    }
}
