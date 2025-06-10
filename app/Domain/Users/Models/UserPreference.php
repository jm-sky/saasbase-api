<?php

namespace App\Domain\Users\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ?string $language
 * @property ?string $decimal_separator
 * @property ?string $date_format
 * @property ?string $dark_mode
 * @property ?string $timezone
 * @property bool    $is_sound_enabled
 * @property bool    $is_profile_public
 * @property array   $field_visibility
 * @property array   $visibility_per_tenant
 * @property User    $user
 */
class UserPreference extends BaseModel
{
    public const DEFAULT_FIELD_VISIBILITY = [
        'email'      => 'tenant',
        'phone'      => 'tenant',
        'birth_date' => 'tenant',
    ];

    protected $fillable = [
        'user_id',
        'language',
        'decimal_separator',
        'date_format',
        'dark_mode',
        'is_sound_enabled',
        'is_profile_public',
        'field_visibility',
        'visibility_per_tenant',
    ];

    protected $casts = [
        'is_sound_enabled'      => 'boolean',
        'is_profile_public'     => 'boolean',
        'field_visibility'      => 'array',
        'visibility_per_tenant' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a field is publicly visible.
     */
    public function isFieldPublic(string $field): bool
    {
        return 'public' === $this->getFieldVisibility($field);
    }

    /**
     * Check if a field is visible within the tenant.
     */
    public function isFieldVisibleInTenant(string $field): bool
    {
        $visibility = $this->getFieldVisibility($field);

        return 'public' === $visibility || 'tenant' === $visibility;
    }

    /**
     * Get the visibility level for a field.
     *
     * @return string|null 'public', 'tenant', 'hidden', or null if not set
     */
    public function getFieldVisibility(string $field): ?string
    {
        return $this->field_visibility[$field] ?? self::DEFAULT_FIELD_VISIBILITY[$field] ?? null;
    }

    /**
     * Set the visibility level for a field.
     */
    public function setFieldVisibility(string $field, string $visibility): void
    {
        $this->field_visibility = array_merge($this->field_visibility ?? [], [
            $field => $visibility,
        ]);
    }

    /**
     * Get the visibility level for a field in a specific tenant.
     *
     * @return string|null 'public', 'tenant', 'hidden', or null if not set
     */
    public function getFieldVisibilityForTenant(string $field, string $tenantId): ?string
    {
        return $this->visibility_per_tenant[$tenantId][$field] ?? $this->getFieldVisibility($field);
    }

    /**
     * Set the visibility level for a field in a specific tenant.
     */
    public function setFieldVisibilityForTenant(string $field, string $tenantId, string $visibility): void
    {
        $this->visibility_per_tenant = array_merge($this->visibility_per_tenant ?? [], [
            $tenantId => array_merge($this->visibility_per_tenant[$tenantId] ?? [], [
                $field => $visibility,
            ]),
        ]);
    }
}
