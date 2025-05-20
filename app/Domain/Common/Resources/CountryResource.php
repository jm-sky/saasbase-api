<?php

namespace App\Domain\Common\Resources;

use App\Domain\Common\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Country $this->resource */
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'code'           => $this->code,
            'code3'          => $this->code3,
            'numericCode'    => $this->numeric_code,
            'phoneCode'      => $this->phone_code,
            'capital'        => $this->capital,
            'currency'       => $this->currency,
            'currencyCode'   => $this->currency_code,
            'currencySymbol' => $this->currency_symbol,
            'tld'            => $this->tld,
            'native'         => $this->native,
            'region'         => $this->region,
            'subregion'      => $this->subregion,
            'emoji'          => $this->emoji,
            'emojiU'         => $this->emoji_u,
            'createdAt'      => $this->created_at?->toIso8601String(),
            'updatedAt'      => $this->updated_at?->toIso8601String(),
            'deletedAt'      => $this->deleted_at?->toIso8601String(),
        ];
    }
}
