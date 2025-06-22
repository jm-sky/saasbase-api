<?php

namespace App\Domain\Expense\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Models\OcrRequest;
use App\Domain\Common\Models\Tag;
use App\Domain\Common\Traits\HasActivityLog;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Common\Traits\HasTags;
use App\Domain\Common\Traits\IsSearchable;
use App\Domain\Financial\Casts\BigDecimalCast;
use App\Domain\Financial\Casts\InvoiceBodyCast;
use App\Domain\Financial\Casts\InvoiceOptionsCast;
use App\Domain\Financial\Casts\InvoicePartyCast;
use App\Domain\Financial\Casts\InvoicePaymentCast;
use App\Domain\Financial\DTOs\InvoiceBodyDTO;
use App\Domain\Financial\DTOs\InvoiceOptionsDTO;
use App\Domain\Financial\DTOs\InvoicePartyDTO;
use App\Domain\Financial\DTOs\InvoicePaymentDTO;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\ShareToken\Traits\HasShareTokens;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Brick\Math\BigDecimal;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string            $id
 * @property string            $tenant_id
 * @property InvoiceType       $type
 * @property InvoiceStatus     $status
 * @property string            $number
 * @property BigDecimal        $total_net
 * @property BigDecimal        $total_tax
 * @property BigDecimal        $total_gross
 * @property string            $currency
 * @property BigDecimal        $exchange_rate
 * @property InvoicePartyDTO   $seller
 * @property InvoicePartyDTO   $buyer
 * @property InvoiceBodyDTO    $body
 * @property InvoicePaymentDTO $payment
 * @property InvoiceOptionsDTO $options
 * @property Collection<Tag>   $tags
 * @property OcrRequest        $ocrRequest
 */
class Expense extends BaseModel implements HasMedia
{
    use SoftDeletes;
    use BelongsToTenant;
    use IsSearchable;
    use HasShareTokens;
    use HasTags;
    use InteractsWithMedia;
    use HasActivityLog;
    use HasActivityLogging;

    protected $fillable = [
        'type',
        'issue_date',
        'status',
        'number',
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
        'seller'        => InvoicePartyCast::class,
        'buyer'         => InvoicePartyCast::class,
        'body'          => InvoiceBodyCast::class,
        'payment'       => InvoicePaymentCast::class,
        'options'       => InvoiceOptionsCast::class,
    ];

    public function ocrRequest(): MorphOne
    {
        return $this->morphOne(OcrRequest::class, 'processable');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }

    protected static function newFactory()
    {
        return ExpenseFactory::new();
    }
}
