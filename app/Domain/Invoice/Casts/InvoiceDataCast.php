<?php

namespace App\Domain\Invoice\Casts;

use App\Domain\Invoice\DTOs\InvoiceDataDTO;
use App\Domain\Invoice\Models\Invoice;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class InvoiceDataCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        /** @var Invoice $model */
        $invoice = $model;

        if (is_null($value)) {
            return null;
        }

        $data = json_decode($value, true);

        return InvoiceDataDTO::fromArray($data);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof InvoiceDataDTO) {
            return $value->toJson();
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }
}
