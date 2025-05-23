<?php

namespace App\Domain\Common\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string              $id
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class BaseModel extends Model
{
    use HasUuids;
    use HasFactory;

    protected static function newFactory()
    {
        $factoryClass = 'Database\Factories\\' . class_basename(static::class) . 'Factory';

        return class_exists($factoryClass) ? $factoryClass::new() : null;
    }
}
