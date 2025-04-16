<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends BaseModel
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected $casts = [
        'id' => 'string',
    ];
}
