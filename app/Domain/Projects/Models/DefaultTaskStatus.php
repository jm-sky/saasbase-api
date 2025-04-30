<?php

namespace App\Domain\Projects\Models;

use App\Domain\Common\Models\BaseModel;

class DefaultTaskStatus extends BaseModel
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
