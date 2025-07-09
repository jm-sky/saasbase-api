<?php

namespace Database\Seeders;

use App\Domain\Financial\Models\GtuCode;
use Illuminate\Database\Seeder;

class GTUCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gtuCodes = [
            [
                'code'                  => 'GTU_01',
                'name'                  => 'Napoje alkoholowe',
                'description'           => 'Napoje alkoholowe - piwo, wino, napoje fermentowane i wyroby pośrednie',
                'amount_threshold_pln'  => null,
                'applicable_conditions' => null,
                'is_active'             => true,
                'effective_from'        => '2017-07-01',
                'effective_to'          => null,
            ],
            [
                'code'                  => 'GTU_02',
                'name'                  => 'Towary związane z tytoniem',
                'description'           => 'Towary związane z tytoniem - papierosy, cygaretka, tytoń, płyn do e-papierosów',
                'amount_threshold_pln'  => null,
                'applicable_conditions' => null,
                'is_active'             => true,
                'effective_from'        => '2017-07-01',
                'effective_to'          => null,
            ],
            [
                'code'                  => 'GTU_03',
                'name'                  => 'Paliwa silnikowe',
                'description'           => 'Paliwa silnikowe - benzyna, olej napędowy, gaz LPG',
                'amount_threshold_pln'  => null,
                'applicable_conditions' => null,
                'is_active'             => true,
                'effective_from'        => '2017-07-01',
                'effective_to'          => null,
            ],
            [
                'code'                  => 'GTU_04',
                'name'                  => 'Pojazdy samochodowe',
                'description'           => 'Pojazdy samochodowe - samochody osobowe, dostawcze, motocykle',
                'amount_threshold_pln'  => null,
                'applicable_conditions' => null,
                'is_active'             => true,
                'effective_from'        => '2017-07-01',
                'effective_to'          => null,
            ],
            [
                'code'                  => 'GTU_05',
                'name'                  => 'Urządzenia elektroniczne',
                'description'           => 'Urządzenia elektroniczne - telefony, komputery, tablety',
                'amount_threshold_pln'  => null,
                'applicable_conditions' => null,
                'is_active'             => true,
                'effective_from'        => '2017-07-01',
                'effective_to'          => null,
            ],
            [
                'code'                  => 'GTU_06',
                'name'                  => 'Części i akcesoria do pojazdów',
                'description'           => 'Części i akcesoria do pojazdów - opony, akumulatory, części zamienne',
                'amount_threshold_pln'  => null,
                'applicable_conditions' => null,
                'is_active'             => true,
                'effective_from'        => '2017-07-01',
                'effective_to'          => null,
            ],
            [
                'code'                  => 'GTU_07',
                'name'                  => 'Towary o wartości przekraczającej 50 000 PLN',
                'description'           => 'Towary o wartości przekraczającej 50 000 złotych za sztukę lub komplet',
                'amount_threshold_pln'  => 50000.00,
                'applicable_conditions' => null,
                'is_active'             => true,
                'effective_from'        => '2017-07-01',
                'effective_to'          => null,
            ],
            [
                'code'                  => 'GTU_08',
                'name'                  => 'Metale szlachetne',
                'description'           => 'Metale szlachetne - złoto, srebro, platyna oraz wyroby z tych metali',
                'amount_threshold_pln'  => null,
                'applicable_conditions' => null,
                'is_active'             => true,
                'effective_from'        => '2017-07-01',
                'effective_to'          => null,
            ],
            [
                'code'                  => 'GTU_09',
                'name'                  => 'Lekarstwa i wyroby medyczne',
                'description'           => 'Lekarstwa i wyroby medyczne - leki, sprzęt medyczny',
                'amount_threshold_pln'  => null,
                'applicable_conditions' => null,
                'is_active'             => true,
                'effective_from'        => '2017-07-01',
                'effective_to'          => null,
            ],
            [
                'code'                  => 'GTU_10',
                'name'                  => 'Budynki, budowle i grunty',
                'description'           => 'Budynki, budowle i grunty - nieruchomości oraz prawa do nich',
                'amount_threshold_pln'  => null,
                'applicable_conditions' => null,
                'is_active'             => true,
                'effective_from'        => '2017-07-01',
                'effective_to'          => null,
            ],
            [
                'code'                  => 'GTU_11',
                'name'                  => 'Świadczenia gazowe',
                'description'           => 'Świadczenia gazowe - dostawa gazu ziemnego',
                'amount_threshold_pln'  => null,
                'applicable_conditions' => null,
                'is_active'             => true,
                'effective_from'        => '2017-07-01',
                'effective_to'          => null,
            ],
            [
                'code'                  => 'GTU_12',
                'name'                  => 'Świadczenia energii elektrycznej',
                'description'           => 'Świadczenia energii elektrycznej - dostawa energii elektrycznej',
                'amount_threshold_pln'  => null,
                'applicable_conditions' => null,
                'is_active'             => true,
                'effective_from'        => '2017-07-01',
                'effective_to'          => null,
            ],
            [
                'code'                  => 'GTU_13',
                'name'                  => 'Świadczenia telekomunikacyjne',
                'description'           => 'Świadczenia telekomunikacyjne - usługi telekomunikacyjne',
                'amount_threshold_pln'  => null,
                'applicable_conditions' => null,
                'is_active'             => true,
                'effective_from'        => '2017-07-01',
                'effective_to'          => null,
            ],
        ];

        foreach ($gtuCodes as $gtuData) {
            GtuCode::updateOrCreate(
                ['code' => $gtuData['code']],
                $gtuData
            );
        }
    }
}
