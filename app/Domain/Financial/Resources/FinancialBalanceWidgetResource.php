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
            'currentMonth' => [
                'balance'       => $this->resource['currentMonth']['balance'],
                'changePercent' => $this->resource['currentMonth']['changePercent'],
            ],
            'currentYear' => [
                'balance'       => $this->resource['currentYear']['balance'],
                'changePercent' => $this->resource['currentYear']['changePercent'],
            ],
        ];
    }
}
