<?php

namespace App\Domain\Invoice\Casts;

use App\Domain\Invoice\DTOs\InvoiceSellerDTO;
use App\Domain\Invoice\Models\Invoice;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class InvoiceSellerCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        /** @var Invoice $model */
        $invoice = $model;

        if (is_null($value)) {
            return null;
        }

        $data = json_decode($value, true);

        return InvoiceSellerDTO::fromArray($data);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        return $value->toJson();
    }
}
