<?php

namespace App\Domain\Financial\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialExpensesWidgetResource extends JsonResource
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
                'expenses'      => $this->resource['currentMonth']['expenses'],
                'changePercent' => $this->resource['currentMonth']['changePercent'],
            ],
            'currentYear' => [
                'expenses'      => $this->resource['currentYear']['expenses'],
                'changePercent' => $this->resource['currentYear']['changePercent'],
            ],
        ];
    }
}
