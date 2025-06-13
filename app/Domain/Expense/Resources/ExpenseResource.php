<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Expense\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Expense $this->resource */
        return [
            'id'                  => $this->id,
            'tenantId'            => $this->tenant_id,
            'type'                => $this->type->value,
            'status'              => $this->status->value,
            'number'              => $this->number,
            'totalNet'            => $this->total_net->toFloat(),
            'totalTax'            => $this->total_tax->toFloat(),
            'totalGross'          => $this->total_gross->toFloat(),
            'currency'            => $this->currency,
            'exchangeRate'        => $this->exchange_rate->toFloat(),
            'seller'              => $this->seller->toArray(),
            'buyer'               => $this->buyer->toArray(),
            'body'                => $this->body->toArray(),
            'payment'             => $this->payment->toArray(),
            'options'             => $this->options->toArray(),
            'issueDate'           => $this->issue_date?->toDateString(),
            'createdAt'           => $this->created_at?->toIso8601String(),
            'updatedAt'           => $this->updated_at?->toIso8601String(),
        ];
    }
}
