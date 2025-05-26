<?php

namespace App\Domain\Tenant\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantBrandingRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'colorPrimary'         => ['nullable', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
            'colorSecondary'       => ['nullable', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
            'shortName'            => ['nullable', 'string', 'max:50'],
            'theme'                => ['nullable', 'string', Rule::in(['light', 'dark', 'system'])],
            'pdfAccentColor'       => ['nullable', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
            'emailSignatureHtml'   => ['nullable', 'string'],
            'logo'                 => ['nullable', 'file', 'image', 'max:2048'],
            'favicon'              => ['nullable', 'file', 'image', 'max:1024'],
            'customFont'           => ['nullable', 'file', 'mimes:woff,woff2', 'max:2048'],
            'pdfLogo'              => ['nullable', 'file', 'image', 'max:2048'],
            'emailHeaderImage'     => ['nullable', 'file', 'image', 'max:2048'],
        ];
    }
}
