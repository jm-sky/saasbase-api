<?php

namespace App\Domain\Expense\Models;

use App\Domain\Approval\Models\ApprovalExpenseExecution;
use App\Domain\Auth\Models\User;
use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Models\OcrRequest;
use App\Domain\Common\Models\Tag;
use App\Domain\Common\Traits\HasActivityLog;
use App\Domain\Common\Traits\HasActivityLogging;
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
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string                             $id
 * @property string                             $tenant_id
 * @property InvoiceType                        $type
 * @property InvoiceStatus                      $status
 * @property OcrRequestStatus                   $ocrStatus
 * @property AllocationStatus                   $allocationStatus
 * @property ApprovalStatus                     $approvalStatus
 * @property DeliveryStatus                     $deliveryStatus
 * @property PaymentStatus                      $paymentStatus
 * @property string                             $number
 * @property BigDecimal                         $total_net
 * @property BigDecimal                         $total_tax
 * @property BigDecimal                         $total_gross
 * @property string                             $currency
 * @property BigDecimal                         $exchange_rate
 * @property InvoicePartyDTO                    $seller
 * @property InvoicePartyDTO                    $buyer
 * @property InvoiceBodyDTO                     $body
 * @property InvoicePaymentDTO                  $payment
 * @property InvoiceOptionsDTO                  $options
 * @property Collection<Tag>                    $tags
 * @property User                               $createdByUser
 * @property OcrRequest                         $ocrRequest
 * @property Collection<int, ExpenseAllocation> $allocations
 * @property ApprovalExpenseExecution           $approvalExecution
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
    use IsCreatableByUser;

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

    public function ocrRequest(): MorphOne
    {
        return $this->morphOne(OcrRequest::class, 'processable');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ExpenseAllocation::class);
    }

    public function approvalExecution(): HasOne
    {
        return $this->hasOne(ApprovalExpenseExecution::class, 'expense_id');
    }

    /**
     * Get the total amount allocated across all allocations.
     */
    public function getTotalAllocatedAttribute(): BigDecimal
    {
        return $this->allocations->reduce(
            fn ($carry, $allocation) => $carry->plus($allocation->amount),
            BigDecimal::zero()
        );
    }

    /**
     * Check if the expense is fully allocated.
     */
    public function getIsFullyAllocatedAttribute(): bool
    {
        return $this->total_gross->isEqualTo($this->getTotalAllocatedAttribute());
    }

    /**
     * Check if the expense is partially allocated.
     */
    public function getIsPartiallyAllocatedAttribute(): bool
    {
        $allocated = $this->getTotalAllocatedAttribute();

        return $allocated->isGreaterThan(BigDecimal::zero()) && $allocated->isLessThan($this->total_gross);
    }

    /**
     * Get the remaining amount to be allocated.
     */
    public function getRemainingToAllocateAttribute(): BigDecimal
    {
        return $this->total_gross->minus($this->getTotalAllocatedAttribute());
    }

    /**
     * Check if expense has any allocations.
     */
    public function hasAllocations(): bool
    {
        return $this->allocations()->exists();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
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
        return ExpenseFactory::new();
    }
}
