<?php

namespace App\Domain\Expense\Actions;

use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Common\Models\Media;
use App\Domain\Common\Models\OcrRequest;
use App\Domain\Exchanges\Models\Currency;
use App\Domain\Expense\Models\Expense;
use App\Domain\Financial\DTOs\InvoiceBodyDTO;
use App\Domain\Financial\DTOs\InvoiceExchangeDTO;
use App\Domain\Financial\DTOs\InvoiceOptionsDTO;
use App\Domain\Financial\DTOs\InvoicePartyDTO;
use App\Domain\Financial\DTOs\InvoicePaymentDTO;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Financial\Enums\PaymentMethod;
use App\Domain\Financial\Enums\PaymentStatus;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class CreateExpenseForOcr
{
    public static function handle(UploadedFile $file): Expense
    {
        /** @var Expense $expense */
        $expense = Expense::create([
            'type'                  => InvoiceType::Basic,
            'issue_date'            => Carbon::now(),
            'status'                => InvoiceStatus::OCR_PROCESSING,
            'number'                => '',
            'total_net'             => BigDecimal::of('0'),
            'total_tax'             => BigDecimal::of('0'),
            'total_gross'           => BigDecimal::of('0'),
            'currency'              => Currency::POLISH_CURRENCY_CODE,
            'exchange_rate'         => BigDecimal::of('1.0'),
            'seller'                => new InvoicePartyDTO(),
            'buyer'                 => new InvoicePartyDTO(),
            'body'                  => new InvoiceBodyDTO(
                lines: [],
                vatSummary: [],
                exchange: new InvoiceExchangeDTO(Currency::POLISH_CURRENCY_CODE),
                description: null,
            ),
            'payment'               => new InvoicePaymentDTO(PaymentStatus::PENDING, null, null, null, PaymentMethod::BANK_TRANSFER),
            'options'               => new InvoiceOptionsDTO(),
        ]);

        $media = $expense->addMedia($file)->toMediaCollection('attachments');

        self::createOcrRequest($expense, $media);

        return $expense;
    }

    public static function createOcrRequest(Expense $expense, Media $media): OcrRequest
    {
        return $expense->ocrRequest()->create([
            'tenant_id'            => $expense->tenant_id,
            'media_id'             => $media->id,
            'status'               => OcrRequestStatus::Pending->value,
            'created_by'           => Auth::id(),
            'external_document_id' => null,
            'result'               => null,
            'errors'               => null,
            'started_at'           => null,
            'finished_at'          => null,
        ]);
    }
}
