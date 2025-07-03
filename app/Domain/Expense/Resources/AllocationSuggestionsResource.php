<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Expense\DTOs\AllocationDataDTO;
use App\Domain\Expense\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * Resource for allocation suggestions response.
 *
 * @mixin Expense
 */
class AllocationSuggestionsResource extends JsonResource
{
    private Collection $suggestions;

    private Collection $enabledDimensions;

    public function __construct($expense, Collection $suggestions, Collection $enabledDimensions)
    {
        parent::__construct($expense);
        $this->suggestions       = $suggestions;
        $this->enabledDimensions = $enabledDimensions;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Expense $this->resource */
        return [
            'suggestions'         => array_map(fn (AllocationDataDTO $dto) => $dto->toArray(), $this->suggestions->toArray()),
            'enabledDimensions'   => DimensionTypeResource::collection($this->enabledDimensions),
            'expenseTotal'        => $this->total_gross->toFloat(),
            'currentAllocated'    => $this->total_allocated->toFloat(),
            'remainingToAllocate' => $this->remaining_to_allocate->toFloat(),
        ];
    }
}
