<?php

namespace App\Domain\Projects\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Projects\Database\Factories\TaskStatusFactory;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property string $color
 * @property int    $sort_order
 * @property bool   $is_default
 * @property Task[] $tasks
 */
class TaskStatus extends BaseModel
{
    use BelongsToTenant;

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

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'status_id');
    }

    protected static function newFactory(): TaskStatusFactory
    {
        return TaskStatusFactory::new();
    }
}
