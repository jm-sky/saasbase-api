<?php

namespace App\Domain\Common\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Financial\Casts\DocumentAnalysisResultCast;
use App\Services\AzureDocumentIntelligence\DTOs\DocumentAnalysisResult;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string                  $id
 * @property string                  $processable_type
 * @property string                  $processable_id
 * @property string                  $media_id
 * @property ?string                 $external_document_id
 * @property OcrRequestStatus        $status
 * @property ?DocumentAnalysisResult $result
 * @property ?array                  $errors
 * @property ?Carbon                 $started_at
 * @property ?Carbon                 $finished_at
 * @property string                  $created_by
 * @property Carbon                  $created_at
 * @property Carbon                  $updated_at
 */
class OcrRequest extends BaseModel
{
    protected $fillable = [
        'processable_type',
        'processable_id',
        'media_id',
        'external_document_id',
        'status',
        'result',
        'errors',
        'started_at',
        'finished_at',
        'created_by',
    ];

    protected $casts = [
        'status'      => OcrRequestStatus::class,
        'result'      => DocumentAnalysisResultCast::class,
        'errors'      => 'array',
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function processable(): MorphTo
    {
        return $this->morphTo();
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
