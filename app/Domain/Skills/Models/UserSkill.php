<?php

namespace App\Domain\Skills\Models;

use App\Domain\Auth\Models\User;
use Carbon\Carbon;
use Database\Factories\Domain\Skills\UserSkillFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property string  $user_id
 * @property string  $skill_id
 * @property int     $level
 * @property ?Carbon $acquired_at
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property User    $user
 * @property Skill   $skill
 */
class UserSkill extends Pivot
{
    use HasUlids;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'skill_id',
        'level',
        'acquired_at',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'level'       => 'integer',
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

    protected static function newFactory(): UserSkillFactory
    {
        return UserSkillFactory::new();
    }
}
