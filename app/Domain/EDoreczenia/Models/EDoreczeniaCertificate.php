<?php

namespace App\Domain\EDoreczenia\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EDoreczeniaCertificate extends Model
{
    use HasUuids;
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
