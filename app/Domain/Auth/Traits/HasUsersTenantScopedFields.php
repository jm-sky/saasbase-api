<?php

namespace App\Domain\Auth\Traits;

use App\Domain\Auth\Models\User;
use App\Domain\Users\Models\UserPreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;

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
            get: fn ($value) => $this->isFieldVisibleToViewer('email') ? $this->email : null,
        );
    }

    protected function tenantScopedBirthDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->isFieldVisibleToViewer('birth_date') ? $this->profile?->birth_date : null,
        );
    }

    protected function tenantScopedPhone(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->isFieldVisibleToViewer('phone') ? $this->phone : null,
        );
    }

    /**
     * UserPreference::isFieldVisibleInTenant() only looks at the stored
     * 'public'|'tenant'|'hidden' setting — it never checks whether the
     * viewer is actually IN a shared tenant with this user. Combined with
     * 'tenant' being the default visibility for email/phone/birth_date,
     * that made these fields visible to any authenticated user in the
     * entire system, not just co-workers. This checks the viewer's tenant
     * membership before honoring a 'tenant'-level visibility setting.
     */
    private function isFieldVisibleToViewer(string $field): bool
    {
        $visibility = $this->preferences?->getFieldVisibility($field);

        if ($visibility === 'public') {
            return true;
        }

        if ($visibility !== null && $visibility !== 'tenant') {
            return false;
        }

        /** @var ?User $viewer */
        $viewer = Auth::user();

        if (! $viewer) {
            return false;
        }

        if ($viewer->is($this)) {
            return true;
        }

        return $this->tenants()
            ->whereIn('tenants.id', $viewer->tenants()->pluck('tenants.id'))
            ->exists();
    }
}
