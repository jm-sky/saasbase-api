<?php

namespace App\Domain\Financial\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;

/**
 * @property string  $id
 * @property string  $code
 * @property string  $name
 * @property string  $description
 * @property ?float  $amount_threshold_pln
 * @property ?array  $applicable_conditions
 * @property bool    $is_active
 * @property Carbon  $effective_from
 * @property ?Carbon $effective_to
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class GtuCode extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'amount_threshold_pln',
        'applicable_conditions',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'applicable_conditions' => 'array',
        'is_active'             => 'boolean',
        'effective_from'        => 'date',
        'effective_to'          => 'date',
    ];

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEffectiveAt($query, Carbon $date)
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date)
                ;
            })
        ;
    }

    public function scopeWithAmountThreshold($query)
    {
        return $query->whereNotNull('amount_threshold_pln');
    }

    public function isEffectiveAt(Carbon $date): bool
    {
        if ($this->effective_from && $date->isBefore($this->effective_from)) {
            return false;
        }

        if ($this->effective_to && $date->isAfter($this->effective_to)) {
            return false;
        }

        return true;
    }

    public function hasAmountThreshold(): bool
    {
        return null !== $this->amount_threshold_pln;
    }

    public function meetsAmountThreshold(float $amount): bool
    {
        if (!$this->hasAmountThreshold()) {
            return true;
        }

        return $amount >= $this->amount_threshold_pln;
    }

    public function getApplicableConditions(): array
    {
        return $this->applicable_conditions ?? [];
    }
}
