<?php

namespace App\Domain\Auth\Traits;

use App\Domain\Auth\Models\User;
use App\Domain\Users\Models\UserPreference;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @mixin User
 *
 * @property ?UserPreference $preferences
 */
trait HasUsersTenantScopedFields
{
    protected function tenantScopedEmail(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->preferences?->isFieldVisibleInTenant('email') ? $this->email : null,
        );
    }

    protected function tenantScopedBirthDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->preferences?->isFieldVisibleInTenant('birth_date') ? $this->profile?->birth_date : null,
        );
    }

    protected function tenantScopedPhone(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->preferences?->isFieldVisibleInTenant('phone') ? $this->phone : null,
        );
    }
}
