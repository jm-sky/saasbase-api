<?php

namespace App\Domain\Common\Models;

use App\Domain\Products\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                                                 $id
 * @property string                                                 $name
 * @property float                                                  $rate
 * @property Carbon                                                 $created_at
 * @property Carbon                                                 $updated_at
 * @property ?Carbon                                                $deleted_at
 * @property \Illuminate\Database\Eloquent\Collection<int, Product> $products
 */
class VatRate extends BaseModel
{
    protected $fillable = [
        'name',
        'rate',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate' => 'decimal:2',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
