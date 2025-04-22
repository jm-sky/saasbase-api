<?php

namespace App\Domain\Products\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Models\MeasurementUnit;
use App\Domain\Common\Models\VatRate;
use App\Domain\Tenant\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string          $id
 * @property string          $tenant_id
 * @property string          $name
 * @property ?string         $description
 * @property string          $unit_id
 * @property float           $price_net
 * @property string          $vat_rate_id
 * @property Carbon          $created_at
 * @property Carbon          $updated_at
 * @property ?Carbon         $deleted_at
 * @property MeasurementUnit $unit
 * @property VatRate         $vatRate
 */
class Product extends BaseModel
{
    use SoftDeletes;
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'unit_id',
        'price_net',
        'vat_rate_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price_net' => 'decimal:2',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MeasurementUnit::class);
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }

    protected static function newFactory()
    {
        return ProductFactory::new();
    }
}
