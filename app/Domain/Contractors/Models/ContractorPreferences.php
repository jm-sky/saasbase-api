<?php

namespace App\Domain\Contractors\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Exchanges\Models\Currency;
use App\Domain\Financial\Models\PaymentMethod;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string         $id
 * @property string         $tenant_id
 * @property string         $contractor_id
 * @property ?string        $default_payment_method_id
 * @property ?string        $default_currency_code
 * @property ?string        $default_language
 * @property ?int           $default_payment_days
 * @property ?array         $default_tags
 * @property ?PaymentMethod $defaultPaymentMethod
 * @property ?Currency      $defaultCurrency
 */
class ContractorPreferences extends BaseModel
{
    use BelongsToTenant;

    protected $with = [
        'defaultPaymentMethod',
        'defaultCurrency',
    ];

    protected $fillable = [
        'tenant_id',
        'contractor_id',
        'default_payment_method_id',
        'default_currency_code',
        'default_language',
        'default_payment_days',
        'default_tags',
    ];

    protected $casts = [
        'default_payment_days' => 'integer',
        'default_tags'         => 'array',
    ];

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function defaultPaymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'default_payment_method_id');
    }

    public function defaultCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'default_currency_code', 'code');
    }
}
