<?php

namespace App\Domain\Financial\Casts;

use App\Domain\Financial\DTOs\InvoiceBuyerDTO;
use App\Domain\Invoice\Models\Invoice;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class InvoiceBuyerCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        /** @var Invoice $model */
        $invoice = $model;

        if (is_null($value)) {
            return null;
        }

        $data = json_decode($value, true);

        return InvoiceBuyerDTO::fromArray($data);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof InvoiceBuyerDTO) {
            return $value->toJson();
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }
}
