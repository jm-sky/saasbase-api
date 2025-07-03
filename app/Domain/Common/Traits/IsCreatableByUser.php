<?php

namespace App\Domain\Common\Traits;

use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin Model
 */
trait IsCreatableByUser
{
    /**
     * Boot the trait.
     */
    public static function bootIsCreatableByUser(): void
    {
        static::creating(function (Model $model) {
            // @phpstan-ignore-next-line
            if (!$model->created_by_user_id) {
                // @phpstan-ignore-next-line
                $model->created_by_user_id = Auth::id();
            }
        });
    }

    /**
     * Define the belongs to relationship to User model.
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
