<?php

namespace App\Domain\Skills\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $category
 * @property string $name
 * @property ?string $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 *
 * @property-read SkillCategory $skillCategory
 * @property-read Collection<int, UserSkill> $userSkills
 * @property-read Collection<int, ProjectRequiredSkill> $projectRequiredSkills
 */
class Skill extends BaseModel
{
    protected $fillable = [
        'category',
        'name',
        'description',
    ];

    public function skillCategory(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class, 'category', 'name');
    }

    public function userSkills(): HasMany
    {
        return $this->hasMany(UserSkill::class);
    }

    public function projectRequiredSkills(): HasMany
    {
        return $this->hasMany(\App\Domain\Projects\Models\ProjectRequiredSkill::class);
    }
}
