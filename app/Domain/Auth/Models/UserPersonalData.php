<?php

namespace App\Domain\Auth\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $user_id
 * @property string $gender
 * @property string $pesel
 * @property bool   $is_gender_verified
 * @property bool   $is_birth_date_verified
 * @property bool   $is_pesel_verified
 */
class UserPersonalData extends BaseModel
{
    protected $fillable = [
        'user_id',
        'gender',
        'pesel',
        'is_gender_verified',
        'is_birth_date_verified',
        'is_pesel_verified',
    ];

    protected $casts = [
        'is_gender_verified'     => 'boolean',
        'is_birth_date_verified' => 'boolean',
        'is_pesel_verified'      => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
