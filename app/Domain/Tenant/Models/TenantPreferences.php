<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property?string $id
 *
 * @property ?string $currency
 * @property bool    $require_2fa
 * @property bool    $invoice_auto_numbering
 * @property bool    $contractor_logo_fetching
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property ?Tenant $tenant
 */
class TenantPreferences extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'currency',
        'require_2fa',
        'invoice_auto_numbering',
        'contractor_logo_fetching',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
