<?php

namespace App\Domain\EDoreczenia\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $provider
 * @property string $file_path
 * @property string $fingerprint
 * @property string $subject_cn
 * @property Carbon $valid_from
 * @property Carbon $valid_to
 * @property string $created_by
 * @property Tenant $tenant
 * @property User   $creator
 */
class EDoreczeniaCertificate extends BaseModel
{
    use SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'provider',
        'file_path',
        'fingerprint',
        'subject_cn',
        'valid_from',
        'valid_to',
        'created_by',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_to'   => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
