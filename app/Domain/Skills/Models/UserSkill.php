<?php

namespace App\Domain\Skills\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @property string $user_id
 * @property string $skill_id
 * @property int $level
 * @property ?Carbon $acquired_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 *
 * @property-read User $user
 * @property-read Skill $skill
 */
class UserSkill extends BaseModel
{
    protected array $fillable = [
        'user_id',
        'skill_id',
        'level',
        'acquired_at',
    ];

    protected array $casts = [
        'level' => 'integer',
        'acquired_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
