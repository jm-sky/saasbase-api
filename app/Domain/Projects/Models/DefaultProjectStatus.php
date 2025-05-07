<?php

namespace App\Domain\Projects\Models;

use App\Domain\Common\Models\BaseModel;

/**
 * @property string $id
 * @property string $name
 * @property string $color
 * @property int    $sort_order
 * @property string $category
 * @property bool   $is_default
 */
class DefaultProjectStatus extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'color',
        'sort_order',
        'category',
        'is_default',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_default' => 'boolean',
    ];
}
