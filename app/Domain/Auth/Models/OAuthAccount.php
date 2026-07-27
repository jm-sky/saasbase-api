<?php

namespace App\Domain\Auth\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OAuthAccount extends BaseModel
{
    use HasFactory;

    /**
     * Laravel's inflection turns OAuthAccount into o_auth_accounts;
     * the schema (and DBML) uses user_oauth_accounts.
     */
    protected $table = 'user_oauth_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'email',
        'linked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'linked_at' => 'datetime',
    ];

    /**
     * Get the user that owns the OAuth account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
