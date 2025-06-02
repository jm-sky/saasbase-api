<?php

namespace App\Domain\Subscription\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanFeatureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->feature->id,
            'name'         => $this->feature->name,
            'description'  => $this->feature->description,
            'type'         => $this->feature->type,
            'value'        => $this->value,
            'defaultValue' => $this->feature->default_value,
            'createdAt'    => $this->feature->created_at,
            'updatedAt'    => $this->feature->updated_at,
        ];
    }
}
