<?php

namespace App\Domain\Invoice\Requests;

use App\Domain\Template\Services\InvoiceGeneratorService;
use App\Http\Requests\BaseFormRequest;

class InvoicePdfRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'templateId'  => ['sometimes', 'nullable', 'string', 'exists:invoice_templates,id'],
            'collection'  => ['sometimes', 'string', 'max:255'],
            'action'      => ['sometimes', 'string', 'in:download,stream,attach,preview'],
            'language'    => ['sometimes', 'string', 'in:' . implode(',', config('app.supported_locales'))],
        ];
    }

    /**
     * Get the validated template ID.
     */
    public function getTemplateId(): ?string
    {
        return $this->validated('template_id');
    }

    /**
     * Get the validated collection name.
     */
    public function getCollection(): string
    {
        return $this->validated('collection', InvoiceGeneratorService::COLLECTION);
    }

    /**
     * Get the validated action.
     */
    public function getAction(): string
    {
        return $this->validated('action', 'attach');
    }

    /**
     * Get the validated language.
     */
    public function getLanguage(): string
    {
        return $this->validated('language', 'pl');
    }
}
