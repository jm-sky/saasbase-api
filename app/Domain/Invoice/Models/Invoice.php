<?php

namespace App\Domain\Invoice\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Traits\IsSearchable;
use App\Domain\Invoice\Casts\BigDecimalCast;
use App\Domain\Invoice\Casts\InvoiceBuyerCast;
use App\Domain\Invoice\Casts\InvoiceBodyCast;
use App\Domain\Invoice\Casts\InvoiceOptionsCast;
use App\Domain\Invoice\Casts\InvoicePaymentCast;
use App\Domain\Invoice\Casts\InvoiceSellerCast;
use App\Domain\Invoice\DTOs\InvoiceBuyerDTO;
use App\Domain\Invoice\DTOs\InvoiceDataDTO;
use App\Domain\Invoice\DTOs\InvoiceOptionsDTO;
use App\Domain\Invoice\DTOs\InvoicePaymentDTO;
use App\Domain\Invoice\DTOs\InvoiceSellerDTO;
use App\Domain\Invoice\Enums\InvoiceStatus;
use App\Domain\Invoice\Enums\InvoiceType;
use App\Domain\ShareToken\Traits\HasShareTokens;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Brick\Math\BigDecimal;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string            $id
 * @property string            $tenant_id
 * @property InvoiceType       $type
 * @property InvoiceStatus     $status
 * @property string            $number
 * @property string            $numbering_template_id
 * @property BigDecimal        $total_net
 * @property BigDecimal        $total_tax
 * @property BigDecimal        $total_gross
 * @property string            $currency
 * @property BigDecimal        $exchange_rate
 * @property InvoiceSellerDTO  $seller
 * @property InvoiceBuyerDTO   $buyer
 * @property InvoiceDataDTO    $body
 * @property InvoicePaymentDTO $payment
 * @property InvoiceOptionsDTO $options
 */
class Invoice extends BaseModel
{
    use SoftDeletes;
    use BelongsToTenant;
    use IsSearchable;
    use HasShareTokens;

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
        'body',
        'payment',
        'options',
    ];

    protected $casts = [
        'type'          => InvoiceType::class,
        'status'        => InvoiceStatus::class,
        'issue_date'    => 'date',
        'total_net'     => BigDecimalCast::class,
        'total_tax'     => BigDecimalCast::class,
        'total_gross'   => BigDecimalCast::class,
        'exchange_rate' => BigDecimalCast::class,
        'seller'        => InvoiceSellerCast::class,
        'buyer'         => InvoiceBuyerCast::class,
        'body'          => InvoiceBodyCast::class,
        'payment'       => InvoicePaymentCast::class,
        'options'       => InvoiceOptionsCast::class,
    ];

    public function numberingTemplate(): BelongsTo
    {
        return $this->belongsTo(NumberingTemplate::class);
    }

    protected static function newFactory()
    {
        return InvoiceFactory::new();
    }
}
