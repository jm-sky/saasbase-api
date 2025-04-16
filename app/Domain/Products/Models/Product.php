<?php

namespace App\Domain\Products\Models;

use App\Domain\Common\Models\{BaseModel, Unit, VatRate};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property ?string $description
 * @property string $unit_id
 * @property float $price_net
 * @property string $vat_rate_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 *
 * @property-read Unit $unit
 * @property-read VatRate $vatRate
 */
class Product extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'unit_id',
        'price_net',
        'vat_rate_id',
    ];

    protected array $casts = [
        'price_net' => 'decimal:2',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }
}
