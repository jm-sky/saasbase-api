<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Builders\OrgUnitUserBuilder;
use App\Domain\Tenant\Enums\OrgUnitRole;
use App\Domain\Tenant\Enums\UnitRoleLevel;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string           $id
 * @property string           $tenant_id
 * @property string           $organization_unit_id
 * @property string           $user_id
 * @property ?string          $position_id
 * @property OrgUnitRole      $role
 * @property ?UnitRoleLevel   $workflow_role_level
 * @property bool             $is_primary
 * @property bool             $is_active
 * @property Carbon           $valid_from
 * @property ?Carbon          $valid_until
 * @property ?string          $notes
 * @property Carbon           $created_at
 * @property Carbon           $updated_at
 * @property User             $user
 * @property OrganizationUnit $organizationUnit
 * @property ?Position        $position
 *
 * @method static OrgUnitUserBuilder query()
 * @method static OrgUnitUserBuilder newModelQuery()
 * @method static OrgUnitUserBuilder newQuery()
 */
class OrgUnitUser extends BaseModel
{
    use HasFactory;
    use BelongsToTenant;

    protected $table = 'org_unit_user';

    protected $fillable = [
        'tenant_id',
        'organization_unit_id',
        'user_id',
        'position_id',
        'role',
        'workflow_role_level',
        'is_primary',
        'is_active',
        'valid_from',
        'valid_until',
        'notes',
    ];

    protected $casts = [
        'role'                => OrgUnitRole::class,
        'workflow_role_level' => UnitRoleLevel::class,
        'is_primary'          => 'boolean',
        'is_active'           => 'boolean',
        'valid_from'          => 'datetime',
        'valid_until'         => 'datetime',
    ];

    protected $attributes = [
        'is_primary' => false,
        'valid_from' => 'now',
    ];

    /**
     * Create a new Eloquent query builder for the model.
     */
    public function newEloquentBuilder($query): OrgUnitUserBuilder
    {
        return new OrgUnitUserBuilder($query);
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
