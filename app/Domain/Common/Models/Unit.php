<?php

namespace App\Domain\Common\Models;

use App\Domain\Products\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $code
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 */
class Unit extends BaseModel
{
    protected array $fillable = [
        'code',
        'name',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
