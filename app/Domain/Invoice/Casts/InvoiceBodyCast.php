<?php

namespace App\Domain\Invoice\Casts;

use App\Domain\Invoice\DTOs\InvoiceBodyDTO;
use App\Domain\Invoice\Models\Invoice;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class InvoiceBodyCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        /** @var Invoice $model */
        $invoice = $model;

        if (is_null($value)) {
            return null;
        }

        $data = json_decode($value, true);

        return InvoiceBodyDTO::fromArray($data);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof InvoiceBodyDTO) {
            return $value->toJson();
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }
}
