<?php

namespace App\Domain\Contractors\Requests;

use App\Domain\Common\Traits\HasAttachmentMimeWhitelist;
use App\Http\Requests\BaseFormRequest;

class ContractorAttachmentRequest extends BaseFormRequest
{
    use HasAttachmentMimeWhitelist;

    public function authorize(): bool
    {
        return true; // Add authorization logic if needed
    }

    public function rules(): array
    {
        $maxSize = config('domains.contractors.attachments.max_size', 10240); // in kilobytes

        return [
            'file' => ['required', 'file', 'max:'.$maxSize, 'mimetypes:'.implode(',', $this->allowedAttachmentMimes())],
        ];
    }
}
