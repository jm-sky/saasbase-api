<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string     $id
 * @property string     $name
 * @property string     $description
 * @property string     $type
 * @property string     $default_value
 * @property Collection $planFeatures
 */
class Feature extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'default_value',
    ];

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function getValueAttribute($value)
    {
        if ('unlimited' === $value) {
            return 'unlimited';
        }

        return match ($this->type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default   => $value,
        };
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = match ($this->type) {
            'integer' => (string) $value,
            'boolean' => $value ? 'true' : 'false',
            default   => (string) $value,
        };

        return $this->getValueAttribute($value);
    }
}
