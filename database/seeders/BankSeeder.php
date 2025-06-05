<?php

namespace Database\Seeders;

use App\Domain\Bank\Models\Bank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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

        collect($banks)
            ->map(fn ($bank) => [
                'id'           => Str::ulid(),
                'country'      => 'PL',
                'bank_name'    => $bank['name'],
                'branch_name'  => $bank['branch_name'] ?? null,
                'bank_code'    => substr($bank['routing_code'], 0, 4),
                'routing_code' => $bank['routing_code'],
                'swift'        => $bank['swift'] ?? null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ])
            ->chunk(100)
            ->each(function (Collection $chunk) {
                Bank::insert($chunk->all());
            })
        ;
    }
}
