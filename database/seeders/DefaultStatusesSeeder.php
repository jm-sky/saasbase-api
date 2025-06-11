<?php

namespace Database\Seeders;

use App\Domain\Projects\Models\DefaultProjectStatus;
use App\Domain\Projects\Models\DefaultTaskStatus;
use App\Helpers\Ulid;
use Illuminate\Database\Seeder;

class DefaultStatusesSeeder extends Seeder
{
    public function run(): void
    {
        $defaultStatuses = [
            [
                'name'       => 'To do',
                'color'      => '#E5E7EB',
                'sort_order' => 1,
                'is_default' => true,
            ],
            [
                'name'       => 'In progress',
                'color'      => '#60A5FA',
                'sort_order' => 2,
                'is_default' => false,
            ],
            [
                'name'       => 'Done',
                'color'      => '#34D399',
                'sort_order' => 3,
                'is_default' => false,
            ],
        ];

        foreach ($defaultStatuses as $status) {
            DefaultProjectStatus::create([
                'id' => Ulid::deterministic(['project-status', $status['name']]),
                ...$status,
            ]);

            DefaultTaskStatus::create([
                'id' => Ulid::deterministic(['task-status', $status['name']]),
                ...$status,
            ]);
        }
    }
}
