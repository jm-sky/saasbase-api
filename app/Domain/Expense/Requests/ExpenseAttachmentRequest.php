<?php

namespace App\Domain\Expense\Requests;

use App\Domain\Common\Traits\HasAttachmentMimeWhitelist;
use App\Http\Requests\BaseFormRequest;

class ExpenseAttachmentRequest extends BaseFormRequest
{
    use HasAttachmentMimeWhitelist;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxSize = config('domains.expenses.attachments.max_size', 10240); // in kilobytes

        return [
            'file' => ['required', 'file', 'max:' . $maxSize, 'mimetypes:' . implode(',', $this->allowedAttachmentMimes())],
        ];
    }
}
