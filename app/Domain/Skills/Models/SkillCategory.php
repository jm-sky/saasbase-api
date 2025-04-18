<?php

namespace App\Domain\Skills\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                 $id
 * @property string                 $name
 * @property ?string                $description
 * @property Carbon                 $created_at
 * @property Carbon                 $updated_at
 * @property ?Carbon                $deleted_at
 * @property Collection<int, Skill> $skills
 */
class SkillCategory extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class, 'category_id');
    }
}
