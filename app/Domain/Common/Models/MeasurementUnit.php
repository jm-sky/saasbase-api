<?php

namespace App\Domain\Common\Models;

use App\Domain\Products\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                                                 $id
 * @property string                                                 $code
 * @property string                                                 $name
 * @property Carbon                                                 $created_at
 * @property Carbon                                                 $updated_at
 * @property \Illuminate\Database\Eloquent\Collection<int, Product> $products
 */
class MeasurementUnit extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'unit_id');
    }
}
