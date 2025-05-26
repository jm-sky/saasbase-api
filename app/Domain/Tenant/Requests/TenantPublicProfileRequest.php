<?php

namespace App\Domain\Tenant\Requests;

use App\Http\Requests\BaseFormRequest;

class TenantPublicProfileRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'publicName'              => ['required', 'string', 'max:255'],
            'description'             => ['nullable', 'string'],
            'websiteUrl'              => ['nullable', 'url', 'max:255'],
            'socialLinks'             => ['nullable', 'array'],
            'socialLinks.*.platform'  => ['required_with:social_links', 'string', 'max:50'],
            'socialLinks.*.url'       => ['required_with:social_links', 'url', 'max:255'],
            'visible'                 => ['boolean'],
            'industry'                => ['nullable', 'string', 'max:100'],
            'locationCity'            => ['nullable', 'string', 'max:100'],
            'locationCountry'         => ['nullable', 'string', 'max:2'],
            'address'                 => ['nullable', 'string'],
            'publicLogo'              => ['nullable', 'file', 'image', 'max:2048'],
            'bannerImage'             => ['nullable', 'file', 'image', 'max:2048'],
        ];
    }
}
