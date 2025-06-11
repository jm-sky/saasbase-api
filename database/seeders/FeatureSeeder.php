<?php

namespace Database\Seeders;

use App\Domain\Subscription\Enums\FeatureName;
use App\Domain\Subscription\Models\Feature;
use App\Helpers\Ulid;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        foreach (FeatureName::cases() as $feature) {
            Feature::create([
                'id'            => Ulid::deterministic(['feature', $feature->value]),
                'name'          => $feature->value,
                'description'   => $feature->description(),
                'type'          => $feature->type(),
                'default_value' => $feature->defaultValue(),
            ]);
        }
    }
}
