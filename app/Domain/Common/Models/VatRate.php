<?php

namespace App\Domain\Common\Models;

use App\Domain\Products\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property float $rate
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 */
class VatRate extends BaseModel
{
    protected $fillable = [
        'name',
        'rate',
    ];

    protected array $casts = [
        'rate' => 'decimal:2',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
