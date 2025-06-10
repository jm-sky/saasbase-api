<?php

namespace App\Domain\Auth\Traits;

use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @mixin User
 *
 * @property ?UserPreference $preferences
 */
trait HasUsersPublicScopedFields
{
    protected function publicEmail(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->preferences?->isFieldPublic('email') ? $this->email : null,
        );
    }

    protected function publicBirthDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->preferences?->isFieldPublic('birth_date') ? $this->profile?->birth_date : null,
        );
    }

    protected function publicPhone(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->preferences?->isFieldPublic('phone') ? $this->phone : null,
        );
    }
}
