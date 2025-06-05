<?php

namespace App\Domain\Auth\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
