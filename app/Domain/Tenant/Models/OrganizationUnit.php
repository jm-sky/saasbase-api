<?php

// app/Domain/Tenant/Models/OrganizationUnit.php

namespace App\Domain\Tenant\Models;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrganizationUnit extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'tenant_id', 'parent_id', 'name', 'short_name'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id ??= (string) Str::ulid();
        });
    }

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
