<?php

namespace App\Domain\Tenant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Enums\OrgUnitRole;

class OrgUnitUser extends Model
{
    use HasFactory;

    protected $table = 'org_unit_user';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'organization_unit_id', 'user_id', 'role'];

    protected $casts = [
        'role' => OrgUnitRole::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id ??= (string) Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }
}
