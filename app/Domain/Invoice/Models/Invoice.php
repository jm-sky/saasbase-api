<?php

namespace App\Domain\Invoice\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Invoice\Casts\InvoiceBuyerCast;
use App\Domain\Invoice\Casts\InvoiceDataCast;
use App\Domain\Invoice\Casts\InvoiceOptionsCast;
use App\Domain\Invoice\Casts\InvoicePaymentCast;
use App\Domain\Invoice\Casts\InvoiceSellerCast;
use App\Domain\Invoice\DTOs\InvoiceBuyerDTO;
use App\Domain\Invoice\DTOs\InvoiceDataDTO;
use App\Domain\Invoice\DTOs\InvoiceOptionsDTO;
use App\Domain\Invoice\DTOs\InvoicePaymentDTO;
use App\Domain\Invoice\DTOs\InvoiceSellerDTO;
use App\Domain\Invoice\Enums\InvoiceType;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string            $id
 * @property string            $tenant_id
 * @property InvoiceType       $type
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
class Invoice extends BaseModel
{
    use SoftDeletes;
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
        'type'          => InvoiceType::class,
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
