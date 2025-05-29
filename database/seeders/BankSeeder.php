<?php

namespace Database\Seeders;

use App\Domain\Bank\Models\Bank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/nbp_banks.json');

        if (!File::exists($jsonPath)) {
            $this->command->error('nbp_banks.json not found in database/data directory');

            return;
        }

        $banks = json_decode(File::get($jsonPath), true);

        foreach ($banks as $bank) {
            Bank::create([
                'country'      => 'PL',
                'bank_name'    => $bank['name'],
                'branch_name'  => $bank['branch_name'] ?? null,
                'bank_code'    => substr($bank['routing_code'], 0, 4),
                'routing_code' => $bank['routing_code'],
                'swift'        => $bank['swift'] ?? null,
            ]);
        }
    }
}
