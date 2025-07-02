<?php

namespace Database\Seeders;

use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;

class DefaultOrganizationUnitsSeeder extends Seeder
{
    public function run(): void
    {
        $defaultUnits = [
            [
                'code'        => 'management',
                'name'        => 'Zarząd',
                'description' => 'Kierownictwo firmy',
            ],
            [
                'code'        => 'administration',
                'name'        => 'Administracja',
                'description' => 'Działy administracyjne',
            ],
            [
                'code'        => 'sales',
                'name'        => 'Sprzedaż',
                'description' => 'Dział sprzedaży',
            ],
            [
                'code'        => 'production',
                'name'        => 'Produkcja',
                'description' => 'Dział produkcyjny',
            ],
            [
                'code'        => 'it_department',
                'name'        => 'Dział IT',
                'description' => 'Dział informatyczny',
            ],
            [
                'code'        => 'finance',
                'name'        => 'Finanse',
                'description' => 'Dział finansowy i księgowość',
            ],
            [
                'code'        => 'hr',
                'name'        => 'Kadry',
                'description' => 'Dział kadr i płac',
            ],
            [
                'code'        => 'logistics',
                'name'        => 'Logistyka',
                'description' => 'Dział logistyki i magazyn',
            ],
        ];

        foreach ($defaultUnits as $unit) {
            // Create global organization unit (tenant_id = null)
            // Use Tenant::bypassTenant to create global records
            Tenant::bypassTenant(null, function () use ($unit) {
                OrganizationUnit::create([
                    'tenant_id' => null, // Global
                    ...$unit,
                    'is_active' => true,
                ]);
            });
        }
    }
}
