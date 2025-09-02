<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RepairDestination;

class RepairDestinationsSeeder extends Seeder
{
    public function run(): void
    {
        $destinations = [
            'ICTMS',
            'Pantawid ICT',
            'Service Provider',
        ];

        foreach ($destinations as $name) {
            RepairDestination::firstOrCreate(['name' => $name]);
        }
    }
}
