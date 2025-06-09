<?php

namespace App\Domain\Rights\Models;

use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property string  $id
 * @property string  $name
 * @property string  $guard_name
 * @property ?string $tenant_id
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Role extends SpatieRole
{
    use HasUlids;
    use IsGlobalOrBelongsToTenant;

    protected $fillable = ['name', 'guard_name', 'tenant_id'];
}
