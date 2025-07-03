<?php

namespace App\Domain\Financial\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialOverviewWidgetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'year'   => $this->resource['year'],
            'months' => collect($this->resource['months'])->map(function ($month) {
                return [
                    'month'     => $month['month'],
                    'revenue'   => $month['revenue'],
                    'expenses'  => $month['expenses'],
                    'balance'   => $month['balance'],
                ];
            })->values()->all(),
        ];
    }
}
