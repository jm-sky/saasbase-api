<?php

namespace App\Domain\Rights\Models;

use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * @property string  $id
 * @property string  $name
 * @property string  $guard_name
 * @property ?string $tenant_id
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Permission extends SpatiePermission
{
    use HasUlids;
    use IsGlobalOrBelongsToTenant;

    protected $fillable = ['name', 'guard_name', 'tenant_id'];
}
