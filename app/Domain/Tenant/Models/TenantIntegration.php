<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string  $id
 * @property string  $tenant_id
 * @property string  $type
 * @property bool    $enabled
 * @property string  $mode
 * @property ?array  $credentials
 * @property ?array  $meta
 * @property ?Carbon $last_synced_at
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property Tenant  $tenant
 */
class TenantIntegration extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'tenant_id',
        'type',
        'enabled',
        'mode',
        'credentials',
        'meta',
        'last_synced_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enabled'        => 'boolean',
        'credentials'    => 'encrypted:json',
        'meta'           => 'json',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the integration.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Interact with the credentials attribute.
     */
    protected function credentials(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }
}
