<?php

namespace App\Domain\Tenant\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantPublicProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'publicName'      => $this->public_name,
            'description'     => $this->description,
            'websiteUrl'      => $this->website_url,
            'socialLinks'     => $this->social_links,
            'visible'         => $this->visible,
            'industry'        => $this->industry,
            'locationCity'    => $this->location_city,
            'locationCountry' => $this->location_country,
            'address'         => $this->address,
            'publicLogo'      => $this->getMediaSignedUrl('public_logo'),
            'bannerImage'     => $this->getMediaSignedUrl('banner_image'),
        ];
    }
}
