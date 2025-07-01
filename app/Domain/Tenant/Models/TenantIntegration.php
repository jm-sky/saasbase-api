<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Enums\TenantIntegrationType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string                $id
 * @property string                $tenant_id
 * @property TenantIntegrationType $type
 * @property bool                  $enabled
 * @property ?array                $credentials
 * @property ?array                $meta
 * @property ?Carbon               $last_synced_at
 * @property Carbon                $created_at
 * @property Carbon                $updated_at
 * @property Tenant                $tenant
 */
class TenantIntegration extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'type',
        'enabled',
        'credentials',
        'meta',
        'last_synced_at',
    ];

    protected $casts = [
        'type'           => TenantIntegrationType::class,
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
