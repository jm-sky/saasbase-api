<?php

namespace App\Domain\Tenant\Resources;

use App\Domain\Tenant\Models\TenantPreferences;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TenantPreferences
 */
class TenantPreferencesResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'currency'               => $this->currency,
            'require2fa'             => $this->require_2fa,
            'invoiceAutoNumbering'   => $this->invoice_auto_numbering,
            'contractorLogoFetching' => $this->contractor_logo_fetching,
            'createdAt'              => $this->created_at?->toIso8601String(),
            'updatedAt'              => $this->updated_at?->toIso8601String(),
        ];
    }
}
