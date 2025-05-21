<?php

namespace App\Domain\Auth\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * @property string  $id
 * @property string  $inviter_id
 * @property ?string $invited_user_id
 * @property string  $email
 * @property string  $token
 * @property string  $status
 * @property ?Carbon $accepted_at
 * @property Carbon  $expires_at
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property User    $inviter
 * @property ?User   $invitedUser
 */
class ApplicationInvitation extends BaseModel
{
    use Notifiable;

    protected $table = 'application_invitations';

    protected $fillable = [
        'inviter_id',
        'invited_user_id',
        'email',
        'token',
        'status',
        'accepted_at',
        'expires_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at'  => 'datetime',
    ];

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}
