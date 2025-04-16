<?php

namespace App\Domain\Projects\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Skills\Models\Skill;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $project_id
 * @property string $skill_id
 * @property int $required_level
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 *
 * @property-read Project $project
 * @property-read Skill $skill
 */
class ProjectRequiredSkill extends BaseModel
{
    protected $fillable = [
        'project_id',
        'skill_id',
        'required_level',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'required_level' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
