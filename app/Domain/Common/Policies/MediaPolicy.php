<?php

namespace App\Domain\Common\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any media.
     */
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->isCurrentTenant($tenant);
    }

    /**
     * Determine whether the user can view the media.
     */
    public function view(User $user, Media $media, Tenant $tenant): bool
    {
        return $user->isCurrentTenant($tenant)
            && Tenant::class === $media->model_type
            && $media->model_id === $tenant->id;
    }

    /**
     * Determine whether the user can create media.
     */
    public function create(User $user, Tenant $tenant): bool
    {
        return $user->isCurrentTenant($tenant);
    }

    /**
     * Determine whether the user can delete the media.
     */
    public function delete(User $user, Media $media, Tenant $tenant): bool
    {
        return $user->isCurrentTenant($tenant)
            && Tenant::class === $media->model_type
            && $media->model_id === $tenant->id;
    }
}
