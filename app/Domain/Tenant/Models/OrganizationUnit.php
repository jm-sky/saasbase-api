<?php

// app/Domain/Tenant/Models/OrganizationUnit.php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;

/**
 * @property string             $id
 * @property string             $tenant_id
 * @property string             $parent_id
 * @property string             $name
 * @property string             $short_name
 * @property Tenant             $tenant
 * @property OrganizationUnit   $parent
 * @property OrganizationUnit[] $children
 * @property User[]             $users
 * @property OrgUnitUser[]      $orgUnitUsers
 */
class OrganizationUnit extends BaseModel
{
    protected $fillable = [
        'id',
        'tenant_id',
        'parent_id',
        'name',
        'short_name',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function orgUnitUsers()
    {
        return $this->hasMany(OrgUnitUser::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'org_unit_user')
            ->withPivot('role')
            ->withTimestamps()
        ;
    }
}
