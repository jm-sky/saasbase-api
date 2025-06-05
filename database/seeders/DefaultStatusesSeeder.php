<?php

namespace Database\Seeders;

use App\Domain\Projects\Models\DefaultProjectStatus;
use App\Domain\Projects\Models\DefaultTaskStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
                'id' => Str::ulid(),
                ...$status,
            ]);

            DefaultTaskStatus::create([
                'id' => Str::ulid(),
                ...$status,
            ]);
        }
    }
}
