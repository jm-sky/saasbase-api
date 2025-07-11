<?php

namespace App\Domain\Template\Requests;

use App\Http\Requests\BaseFormRequest;

class PreviewInvoiceTemplateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'                   => ['required', 'string'],
            'options'                   => ['sometimes', 'array'],
            'options.language'          => ['sometimes', 'string', 'in:en,pl'],
            'options.currency'          => ['sometimes', 'string'],
            'options.accentColor'       => ['sometimes', 'string'],
            'options.secondaryColor'    => ['sometimes', 'string'],
            'options.includeLogo'       => ['sometimes', 'boolean'],
            'options.includeSignatures' => ['sometimes', 'boolean'],
            'options.dateFormat'        => ['sometimes', 'string'],
            'options.timezone'          => ['sometimes', 'string'],
            'previewData'               => ['required', 'array'],
            'previewData.invoice'       => ['required', 'array'],
            'previewData.options'       => ['required', 'array'],
        ];
    }

    public function getTemplateContent(): string
    {
        return $this->validated('content') ?? '';
    }

    public function getPreviewData(): array
    {
        return $this->validated('previewData') ?? [];
    }

    public function getOptions(): array
    {
        return $this->validated('options') ?? [];
    }

    public function getLanguage(): string
    {
        return $this->validated('options.language') ?? 'en';
    }
}
