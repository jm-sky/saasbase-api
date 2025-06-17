<?php

namespace App\Domain\Financial\Casts;

use App\Domain\Financial\DTOs\InvoicePartyDTO;
use App\Domain\Invoice\Models\Invoice;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class InvoicePartyCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        /** @var Invoice $model */
        $invoice = $model;

        if (is_null($value)) {
            return null;
        }

        $data = json_decode($value, true);

        return InvoicePartyDTO::fromArray($data);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof InvoicePartyDTO) {
            return $value->toJson();
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }
}
