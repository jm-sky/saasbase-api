<?php

namespace App\Domain\Invoice\Casts;

use App\Domain\Invoice\DTOs\InvoicePaymentDTO;
use App\Domain\Invoice\Models\Invoice;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class InvoicePaymentCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        /** @var Invoice $model */
        $invoice = $model;

        if (is_null($value)) {
            return null;
        }

        $data = json_decode($value, true);

        return InvoicePaymentDTO::fromArray($data);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        return $value->toJson();
    }
}
