<?php

namespace App\Domain\Common\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string  $id
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property ?Carbon $deleted_at
 */
class BaseModel extends Model
{
    use HasUlids;
    use HasFactory;

    protected static function newFactory()
    {
        $factoryClass = 'Database\Factories\\' . class_basename(static::class) . 'Factory';

        return class_exists($factoryClass) ? $factoryClass::new() : null;
    }
}
