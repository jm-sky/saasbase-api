<?php

namespace App\Domain\Skills\Resources;

use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\UserSkill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

/**
 * @mixin Skill
 *
 * @property UserSkill $pivot
 */
final class UserSkillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Skill $this->resource */
        return [
            'id'          => $this->pivot->id,
            'userId'      => $this->pivot->user_id,
            'skillId'     => $this->id,
            'category'    => $this->category,
            'name'        => $this->name,
            'description' => $this->description,
            'level'       => $this->pivot->level,
            'acquiredAt'  => $this->pivot->acquired_at?->toDateString(),
            'createdAt'   => $this->created_at?->toIso8601String(),
            'updatedAt'   => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Create a new resource instance.
     */
    public function __construct($resource)
    {
        parent::__construct($resource);

        if ($this->resource->pivot?->wasRecentlyCreated) {
            $this->response()->setStatusCode(Response::HTTP_CREATED);
        }
    }
}
