<?php

namespace App\Domain\Common\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;

trait UsesAllocationMorphMap
{
    protected static function bootUsesAllocationMorphMap()
    {
        Relation::morphMap([
            'HA'  => \App\Domain\Auth\Models\User::class,
            'LO'  => \App\Domain\Common\Models\AllocationLocation::class,
            'PD'  => \App\Domain\Products\Models\AllocationProductCategory::class,
            'PR'  => \App\Domain\Projects\Models\Project::class,
            'RS'  => \App\Domain\Financial\Models\AllocationRevenueType::class,
            'RTR' => \App\Domain\Financial\Models\AllocationTransactionType::class,
            'RY'  => \App\Domain\Financial\Models\AllocationCostType::class,
            'ST'  => \App\Domain\Tenant\Models\OrganizationUnit::class,
            'TP'  => \App\Domain\Financial\Models\AllocationRelatedTransactionCategory::class,
            'UM'  => \App\Domain\Common\Models\AllocationContractType::class,
            'UR'  => \App\Domain\Common\Models\AllocationEquipmentType::class,
        ]);
    }
}
