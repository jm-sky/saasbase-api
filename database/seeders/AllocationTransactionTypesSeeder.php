<?php

namespace Database\Seeders;

use App\Domain\Financial\Models\AllocationTransactionType;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;

class AllocationTransactionTypesSeeder extends Seeder
{
    public function run(): void
    {
        $defaultTypes = [
            [
                'code'        => '10_zakup_materialow_produkcyjnych',
                'name'        => 'Zakup materiałów produkcyjnych',
                'description' => 'Zakup materiałów używanych w procesie produkcyjnym',
            ],
            [
                'code'        => '10_zakup_towarow',
                'name'        => 'Zakup towarów',
                'description' => 'Standardowy zakup towarów handlowych',
            ],
            [
                'code'        => '10_zakup_towarow_odwrotne_obciazenie',
                'name'        => 'Zakup towarów - odwrotne obciążenie',
                'description' => 'Zakup towarów z zastosowaniem mechanizmu odwrotnego obciążenia VAT',
            ],
            [
                'code'        => '20_sprzedaz_towarow',
                'name'        => 'Sprzedaż towarów',
                'description' => 'Standardowa sprzedaż towarów handlowych',
            ],
            [
                'code'        => '30_uslugi_zewnetrzne',
                'name'        => 'Usługi zewnętrzne',
                'description' => 'Zakup usług od zewnętrznych dostawców',
            ],
            [
                'code'        => '40_koszty_biurowe',
                'name'        => 'Koszty biurowe',
                'description' => 'Wydatki związane z działalnością biurową',
            ],
            [
                'code'        => '50_transport_logistyka',
                'name'        => 'Transport i logistyka',
                'description' => 'Koszty transportu i usług logistycznych',
            ],
            [
                'code'        => '60_marketing_reklama',
                'name'        => 'Marketing i reklama',
                'description' => 'Wydatki na działalność marketingową i reklamową',
            ],
        ];

        // Use bypassTenant to create global records (tenant_id = null)
        Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () use ($defaultTypes) {
            foreach ($defaultTypes as $type) {
                AllocationTransactionType::create([
                    'tenant_id' => Tenant::GLOBAL_TENANT_ID, // Explicitly set as global
                    ...$type,
                    'is_active' => true,
                ]);
            }
        });
    }
}
