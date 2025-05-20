<?php

namespace App\Domain\Exchanges\Resources;

use App\Domain\Exchanges\Models\Exchange;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Exchange $this->resource */
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'currency'  => $this->currency,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
