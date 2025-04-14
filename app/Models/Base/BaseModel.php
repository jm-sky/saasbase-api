<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

abstract class BaseModel extends Model
{
    use HasUuids;
    
    protected $keyType = 'string';
    public $incrementing = false;
}
