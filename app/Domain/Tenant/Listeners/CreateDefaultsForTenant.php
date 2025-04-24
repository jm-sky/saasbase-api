<?php

namespace App\Domain\Tenant\Listeners;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;
use App\Domain\Tenant\Events\TenantCreated;
use App\Domain\Tenant\Models\MeasurementUnit;
use Illuminate\Support\Str;

class CreateDefaultsForTenant
{
    public function handle(TenantCreated $event): void
    {
        $tenant = $event->tenant;

        // Create default measurement unit
        MeasurementUnit::create([
            'id'        => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name'      => 'Piece',
            'symbol'    => 'pcs',
        ]);

        // Create default project
        $project = Project::create([
            'id'          => Str::uuid(),
            'tenant_id'   => $tenant->id,
            'name'        => 'Default Project',
            'description' => 'Initial project created with your workspace.',
        ]);

        // Create default task
        Task::create([
            'id'          => Str::uuid(),
            'tenant_id'   => $tenant->id,
            'project_id'  => $project->id,
            'name'        => 'Welcome Task',
            'description' => 'Your first task in the default project.',
        ]);
    }
}
