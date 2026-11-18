<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Opt. Loading Ramp',
            'Opt. EBP',
            'Opt. Press',
            'Opt. Boiler',
        ] as $name) {
            Station::firstOrCreate(['name' => $name]);
        }
    }
}
