<?php

namespace App\Domain\Common\Models;

use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property string $slug
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Tag extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
    ];

    public function taggables(): MorphToMany
    {
        return $this->morphedByMany(
            Model::class,
            'taggable',
            'taggables',
            'tag_id',
            'taggable_id'
        );
    }
}
