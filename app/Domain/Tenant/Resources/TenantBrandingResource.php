<?php

namespace App\Domain\Tenant\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantBrandingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'colorPrimary'       => $this->color_primary,
            'colorSecondary'     => $this->color_secondary,
            'shortName'          => $this->short_name,
            'theme'              => $this->theme,
            'pdfAccentColor'     => $this->pdf_accent_color,
            'emailSignatureHtml' => $this->email_signature_html,
            'logo'               => $this->getMediaSignedUrl('logo'),
            'favicon'            => $this->getMediaSignedUrl('favicon'),
            'customFont'         => $this->getMediaSignedUrl('custom_font'),
            'pdfLogo'            => $this->getMediaSignedUrl('pdf_logo'),
            'emailHeaderImage'   => $this->getMediaSignedUrl('email_header_image'),
        ];
    }
}
