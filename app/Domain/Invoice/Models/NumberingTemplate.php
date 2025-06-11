<?php

namespace App\Domain\Invoice\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Invoice\Enums\InvoiceType;
use App\Domain\Invoice\Enums\ResetPeriod;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string              $id
 * @property ?string             $tenant_id
 * @property string              $name
 * @property InvoiceType         $invoice_type
 * @property string              $format
 * @property int                 $next_number
 * @property ResetPeriod         $reset_period
 * @property string              $prefix
 * @property string              $suffix
 * @property bool                $is_default
 * @property Carbon              $created_at
 * @property Carbon              $updated_at
 * @property Collection<Invoice> $invoices
 */
class NumberingTemplate extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $fillable = [
        'name',
        'invoice_type',
        'format',
        'next_number',
        'reset_period',
        'prefix',
        'suffix',
        'is_default',
    ];

    protected $casts = [
        'next_number'  => 'integer',
        'invoice_type' => InvoiceType::class,
        'reset_period' => ResetPeriod::class,
        'is_default'   => 'boolean',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Generate the next invoice number based on the template format.
     */
    public function generateNextNumber(): string
    {
        $number = str_pad((string) $this->next_number, 3, '0', STR_PAD_LEFT);

        $format = $this->format;
        $format = str_replace('YYYY', date('Y'), $format);
        $format = str_replace('YY', date('y'), $format);
        $format = str_replace('MM', date('m'), $format);
        $format = str_replace('NNNN', str_pad($number, 4, '0', STR_PAD_LEFT), $format);
        $format = str_replace('NNN', $number, $format);

        if ($this->prefix) {
            $format = $this->prefix . $format;
        }

        if ($this->suffix) {
            $format = $format . $this->suffix;
        }

        return $format;
    }

    /**
     * Check if the counter should be reset based on the reset period.
     */
    public function shouldResetCounter(): bool
    {
        return match ($this->reset_period) {
            ResetPeriod::MONTHLY => true,
            ResetPeriod::YEARLY  => '01' === date('m'),
            ResetPeriod::NEVER   => false,
        };
    }
}
