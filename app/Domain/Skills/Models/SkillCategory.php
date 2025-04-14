<?php

namespace App\Domain\Skills\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property ?string $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 *
 * @property-read Collection<int, Skill> $skills
 */
class SkillCategory extends BaseModel
{
    protected array $fillable = [
        'name',
        'description',
    ];

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class, 'category_id');
    }
}
