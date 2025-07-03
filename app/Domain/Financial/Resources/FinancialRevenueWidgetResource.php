<?php

namespace App\Domain\Financial\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialRevenueWidgetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'currentMonth' => [
                'revenue'       => $this->resource['currentMonth']['revenue'],
                'changePercent' => $this->resource['currentMonth']['changePercent'],
            ],
            'currentYear' => [
                'revenue'       => $this->resource['currentYear']['revenue'],
                'changePercent' => $this->resource['currentYear']['changePercent'],
            ],
        ];
    }
}
