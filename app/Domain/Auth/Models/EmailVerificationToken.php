<?php

namespace App\Domain\Auth\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $user_id
 * @property string $token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User   $user
 */
class EmailVerificationToken extends BaseModel
{
    protected $fillable = [
        'user_id',
        'token',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
