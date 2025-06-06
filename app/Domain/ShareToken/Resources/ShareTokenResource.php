<?php

namespace App\Domain\ShareToken\Resources;

use App\Domain\ShareToken\DTOs\ShareTokenDTO;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /** @var ShareTokenDTO $dto */
        $dto = $this->resource instanceof ShareTokenDTO
            ? $this->resource
            : ShareTokenDTO::fromModel($this->resource);

        return $dto->toArray();
    }
}
