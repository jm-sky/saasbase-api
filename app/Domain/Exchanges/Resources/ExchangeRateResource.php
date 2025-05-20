<?php

namespace App\Domain\Exchanges\Resources;

use App\Domain\Exchanges\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var ExchangeRate $this->resource */
        return [
            'id'         => $this->id,
            'exchangeId' => $this->exchange_id,
            'date'       => $this->date->toDateString(),
            'rate'       => $this->rate,
            'table'      => $this->table,
            'source'     => $this->source,
            'createdAt'  => $this->created_at?->toIso8601String(),
        ];
    }
}
