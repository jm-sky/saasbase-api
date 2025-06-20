<?php

namespace App\Domain\Exchanges\Resources;

use App\Domain\Exchanges\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Currency
 */
class CurrencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code'   => $this->code,
            'name'   => $this->name,
            'symbol' => $this->symbol,
        ];
    }
}
