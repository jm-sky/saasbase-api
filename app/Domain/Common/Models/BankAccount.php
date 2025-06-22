<?php

namespace App\Domain\Common\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string                 $id
 * @property ?string                $tenant_id
 * @property string                 $bankable_id
 * @property string                 $bankable_type
 * @property string                 $iban
 * @property ?string                $country
 * @property ?string                $swift
 * @property ?string                $bank_name
 * @property bool                   $is_default
 * @property ?string                $currency
 * @property ?string                $description
 * @property Carbon                 $created_at
 * @property Carbon                 $updated_at
 * @property Tenant                 $tenant
 * @property User|Contractor|Tenant $bankable
 */
class BankAccount extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'tenant_id',
        'bankable_id',
        'bankable_type',
        'iban',
        'country',
        'swift',
        'bank_name',
        'is_default',
        'currency',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the tenant that owns the bank account.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the parent bankable model (User, Contractor, or Tenant).
     */
    public function bankable(): MorphTo
    {
        return $this->morphTo();
    }
}
