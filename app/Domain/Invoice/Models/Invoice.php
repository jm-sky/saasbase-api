<?php

namespace App\Domain\Invoice\Models;

use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Invoice\DTOs\InvoiceDataDTO;
use App\Domain\Invoice\DTOs\InvoiceBuyerDTO;
use App\Domain\Invoice\Casts\InvoiceDataCast;
use App\Domain\Invoice\DTOs\InvoiceSellerDTO;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Invoice\Casts\InvoiceBuyerCast;
use App\Domain\Invoice\DTOs\InvoiceOptionsDTO;
use App\Domain\Invoice\DTOs\InvoicePaymentDTO;
use App\Domain\Invoice\Casts\InvoiceSellerCast;
use App\Domain\Invoice\Casts\InvoiceOptionsCast;
use App\Domain\Invoice\Casts\InvoicePaymentCast;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string            $id
 * @property string            $tenant_id
 * @property string            $type
 * @property string            $status
 * @property string            $number
 * @property string            $numbering_template_id
 * @property BigDecimal        $total_net
 * @property BigDecimal        $total_tax
 * @property BigDecimal        $total_gross
 * @property string            $currency
 * @property BigDecimal        $exchange_rate
 * @property InvoiceSellerDTO  $seller
 * @property InvoiceBuyerDTO   $buyer
 * @property InvoiceDataDTO    $data
 * @property InvoicePaymentDTO $payment
 * @property InvoiceOptionsDTO $options
 */
class Invoice extends Model
{
    use SoftDeletes;
    use HasUuids;
    use BelongsToTenant;

    protected $fillable = [
        'type',
        'issue_date',
        'status',
        'number',
        'numbering_template_id',
        'total_net',
        'total_tax',
        'total_gross',
        'currency',
        'exchange_rate',
        'seller',
        'buyer',
        'data',
        'payment',
        'options',
    ];

    protected $casts = [
        'issue_date'    => 'date',
        'total_net'     => BigDecimal::class,
        'total_tax'     => BigDecimal::class,
        'total_gross'   => BigDecimal::class,
        'exchange_rate' => BigDecimal::class,
        'seller'        => InvoiceSellerCast::class,
        'buyer'         => InvoiceBuyerCast::class,
        'data'          => InvoiceDataCast::class,
        'payment'       => InvoicePaymentCast::class,
        'options'       => InvoiceOptionsCast::class,
    ];

    public function numberingTemplate(): BelongsTo
    {
        return $this->belongsTo(NumberingTemplate::class);
    }
}
