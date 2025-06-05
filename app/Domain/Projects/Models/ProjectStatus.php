<?php

namespace App\Domain\Projects\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Projects\Database\Factories\ProjectStatusFactory;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectStatus extends BaseModel
{
    use BelongsToTenant;
    use HasFactory;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'name',
        'color',
        'sort_order',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'status_id');
    }

    protected static function newFactory(): ProjectStatusFactory
    {
        return ProjectStatusFactory::new();
    }
}
