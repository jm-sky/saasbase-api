<?php

namespace App\Domain\Financial\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Contractors\Models\ContractorPreferences;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Database\Factories\Domain\Financial\Models\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string  $id
 * @property ?string $tenant_id
 * @property string  $name
 * @property ?int    $payment_days
 */
class PaymentMethod extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'payment_days',
    ];

    protected $casts = [
        'payment_days' => 'integer',
    ];

    public function contractorPreferences(): HasMany
    {
        return $this->hasMany(ContractorPreferences::class, 'default_payment_method_id');
    }

    protected static function newFactory()
    {
        return PaymentMethodFactory::new();
    }
}
