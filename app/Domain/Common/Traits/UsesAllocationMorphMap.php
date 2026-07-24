<?php

namespace App\Domain\Common\Traits;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\AllocationContractType;
use App\Domain\Common\Models\AllocationEquipmentType;
use App\Domain\Common\Models\AllocationLocation;
use App\Domain\Financial\Models\AllocationCostType;
use App\Domain\Financial\Models\AllocationRelatedTransactionCategory;
use App\Domain\Financial\Models\AllocationRevenueType;
use App\Domain\Financial\Models\AllocationTransactionType;
use App\Domain\Products\Models\AllocationProductCategory;
use App\Domain\Projects\Models\Project;
use App\Domain\Tenant\Models\OrganizationUnit;
use Illuminate\Database\Eloquent\Relations\Relation;

trait UsesAllocationMorphMap
{
    protected static function bootUsesAllocationMorphMap()
    {
        Relation::morphMap([
            'HA' => User::class,
            'LO' => AllocationLocation::class,
            'PD' => AllocationProductCategory::class,
            'PR' => Project::class,
            'RS' => AllocationRevenueType::class,
            'RTR' => AllocationTransactionType::class,
            'RY' => AllocationCostType::class,
            'ST' => OrganizationUnit::class,
            'TP' => AllocationRelatedTransactionCategory::class,
            'UM' => AllocationContractType::class,
            'UR' => AllocationEquipmentType::class,
        ]);
    }
}
