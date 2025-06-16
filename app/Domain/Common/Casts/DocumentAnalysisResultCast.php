<?php

namespace App\Domain\Common\Casts;

use App\Domain\Common\Models\OcrRequest;
use App\Services\AzureDocumentIntelligence\DTOs\DocumentAnalysisResult;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class DocumentAnalysisResultCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        /** @var OcrRequest $model */
        $ocrRequest = $model;

        if (is_null($value)) {
            return null;
        }

        $data = json_decode($value, true);

        return DocumentAnalysisResult::fromArray($data);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof DocumentAnalysisResult) {
            return $value->toJson();
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }
}
