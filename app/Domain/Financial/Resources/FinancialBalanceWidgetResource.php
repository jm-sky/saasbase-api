<?php

namespace App\Domain\Financial\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialBalanceWidgetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'month' => FinancialWidgetMetricsResource::make($this->resource['month']),
            'year'  => FinancialWidgetMetricsResource::make($this->resource['year']),
        ];
    }
}
