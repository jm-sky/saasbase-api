<?php

namespace App\Domain\Financial\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Financial\DTOs\VatRateDTO;
use App\Domain\Financial\Enums\VatRateType;
use App\Domain\Products\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                   $id
 * @property string                   $name
 * @property float                    $rate
 * @property VatRateType              $type
 * @property string                   $country_code
 * @property bool                     $active
 * @property Carbon                   $valid_from
 * @property Carbon                   $valid_to
 * @property Carbon                   $created_at
 * @property Carbon                   $updated_at
 * @property ?Carbon                  $deleted_at
 * @property Collection<int, Product> $products
 */
class VatRate extends BaseModel
{
    protected $fillable = [
        'name',
        'rate',
        'type',
        'country_code',
        'active',
        'valid_from',
        'valid_to',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate'       => 'decimal:2',
        'type'       => VatRateType::class,
        'active'     => 'boolean',
        'valid_from' => 'date',
        'valid_to'   => 'date',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function toDTO(): VatRateDTO
    {
        return new VatRateDTO(
            id: $this->id,
            name: $this->name,
            rate: $this->rate,
            type: $this->type,
        );
    }
}
