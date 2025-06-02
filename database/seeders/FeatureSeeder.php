<?php

namespace Database\Seeders;

use App\Domain\Subscription\Enums\FeatureName;
use App\Domain\Subscription\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        foreach (FeatureName::cases() as $feature) {
            Feature::create([
                'name'          => $feature->value,
                'description'   => $feature->description(),
                'type'          => $feature->type(),
                'default_value' => $feature->defaultValue(),
            ]);
        }
    }
}
