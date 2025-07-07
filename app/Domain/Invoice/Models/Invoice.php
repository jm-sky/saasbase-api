<?php

namespace App\Domain\Invoice\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Models\Tag;
use App\Domain\Common\Traits\HasTags;
use App\Domain\Common\Traits\IsCreatableByUser;
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
use App\Domain\Financial\Enums\AllocationStatus;
use App\Domain\Financial\Enums\ApprovalStatus;
use App\Domain\Financial\Enums\DeliveryStatus;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Financial\Enums\PaymentStatus;
use App\Domain\ShareToken\Traits\HasShareTokens;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Brick\Math\BigDecimal;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string            $id
 * @property string            $tenant_id
 * @property InvoiceType       $type
 * @property InvoiceStatus     $status
 * @property OcrRequestStatus  $ocrStatus
 * @property AllocationStatus  $allocationStatus
 * @property ApprovalStatus    $approvalStatus
 * @property DeliveryStatus    $deliveryStatus
 * @property PaymentStatus     $paymentStatus
 * @property string            $number
 * @property string            $numbering_template_id
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
 * @property User              $createdByUser
 *
 * @method \Spatie\MediaLibrary\MediaCollections\Models\Media|null getFirstMedia(string $collectionName = 'default')
 * @method \Spatie\MediaLibrary\MediaCollections\FileAdder         addMedia(string|\Symfony\Component\HttpFoundation\File\UploadedFile $file)
 */
class Invoice extends BaseModel implements HasMedia
{
    use SoftDeletes;
    use BelongsToTenant;
    use IsSearchable;
    use HasShareTokens;
    use HasTags;
    use IsCreatableByUser;
    use InteractsWithMedia;

    protected $fillable = [
        'type',
        'issue_date',
        'status',
        'ocr_status',
        'allocation_status',
        'approval_status',
        'delivery_status',
        'payment_status',
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
        'type'              => InvoiceType::class,
        'status'            => InvoiceStatus::class,
        'ocr_status'        => OcrRequestStatus::class,
        'allocation_status' => AllocationStatus::class,
        'approval_status'   => ApprovalStatus::class,
        'delivery_status'   => DeliveryStatus::class,
        'payment_status'    => PaymentStatus::class,
        'issue_date'        => 'date',
        'total_net'         => BigDecimalCast::class,
        'total_tax'         => BigDecimalCast::class,
        'total_gross'       => BigDecimalCast::class,
        'exchange_rate'     => BigDecimalCast::class,
        'seller'            => InvoicePartyCast::class,
        'buyer'             => InvoicePartyCast::class,
        'body'              => InvoiceBodyCast::class,
        'payment'           => InvoicePaymentCast::class,
        'options'           => InvoiceOptionsCast::class,
    ];

    public function numberingTemplate(): BelongsTo
    {
        return $this->belongsTo(NumberingTemplate::class);
    }

    /**
     * Get comprehensive status information as DTO.
     */
    public function getStatusDTO(): \App\Domain\Financial\DTOs\InvoiceStatusDTO
    {
        return new \App\Domain\Financial\DTOs\InvoiceStatusDTO(
            general: $this->status ?? InvoiceStatus::DRAFT,
            ocr: $this->ocr_status,
            allocation: $this->allocation_status,
            approval: $this->approval_status,
            delivery: $this->delivery_status,
            payment: $this->payment_status
        );
    }

    /**
     * Update status using the status service.
     */
    public function updateStatusFromDTO(\App\Domain\Financial\DTOs\InvoiceStatusDTO $statusDTO): void
    {
        $this->update([
            'status'            => $statusDTO->general,
            'ocr_status'        => $statusDTO->ocr,
            'allocation_status' => $statusDTO->allocation,
            'approval_status'   => $statusDTO->approval,
            'delivery_status'   => $statusDTO->delivery,
            'payment_status'    => $statusDTO->payment,
        ]);
    }

    protected static function newFactory()
    {
        return InvoiceFactory::new();
    }
}
