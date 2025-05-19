<?php

namespace App\Domain\Chat\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                       $id
 * @property string                       $tenant_id
 * @property string                       $name
 * @property string                       $type
 * @property ?string                      $description
 * @property Carbon                       $created_at
 * @property Carbon                       $updated_at
 * @property Collection|ChatParticipant[] $participants
 * @property Collection|ChatMessage[]     $messages
 * @property ?Tenant                      $tenant
 */
class ChatRoom extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'description',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(ChatParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isUserParticipant(string $userId): bool
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }
}
