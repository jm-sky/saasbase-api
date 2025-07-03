<?php

namespace App\Domain\Financial\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialWidgetMetricsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'current'       => $this->resource['current'],
            'previous'      => $this->resource['previous'],
            'year'          => $this->resource['year'],
            'changePercent' => $this->resource['changePercent'],
        ];
    }
}
