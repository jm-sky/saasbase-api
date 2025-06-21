<?php

namespace App\Domain\EDoreczenia\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string                                        $id
 * @property string                                        $tenant_id
 * @property string                                        $provider
 * @property string                                        $message_id
 * @property string                                        $subject
 * @property string                                        $content
 * @property string                                        $status
 * @property Carbon                                        $sent_at
 * @property Carbon                                        $delivered_at
 * @property string                                        $created_by
 * @property Tenant                                        $tenant
 * @property User                                          $creator
 * @property Collection<int, EDoreczeniaMessageAttachment> $attachments
 */
class EDoreczeniaMessage extends BaseModel
{
    use SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'provider',
        'message_id',
        'subject',
        'content',
        'status',
        'sent_at',
        'delivered_at',
        'created_by',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EDoreczeniaMessageAttachment::class);
    }
}
