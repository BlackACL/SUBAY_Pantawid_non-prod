<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Official;
use Illuminate\Support\Facades\Event;

class OfficialsSeeder extends Seeder
{
    public function run(): void
    {
        $officials = [
            // Provincial DPSC
            ['province' => 'Davao Occidental', 'role' => 'Provincial DPSC', 'fullname' => 'Jeyson A. Alvarado', 'user_id' => null],
            ['province' => 'Davao Del Sur',    'role' => 'Provincial DPSC', 'fullname' => 'Ana Lou A. Albacite', 'user_id' => null],
            ['province' => 'Davao City',       'role' => 'Provincial DPSC', 'fullname' => 'Ivy Balbuena', 'user_id' => null],
            ['province' => 'Davao Del Norte',  'role' => 'Provincial DPSC', 'fullname' => 'Genevieve N. Jitotowani', 'user_id' => null],
            ['province' => 'Davao De Oro',     'role' => 'Provincial DPSC', 'fullname' => 'Gino Logronio', 'user_id' => null],
            ['province' => 'Davao Oriental',   'role' => 'Provincial DPSC', 'fullname' => 'Mayzel Dawn Rebuyon', 'user_id' => null],

            // Regional DPSC
            ['province' => null, 'role' => 'Regional DPSC', 'fullname' => 'Russell Allen S. Mozo', 'user_id' => null],

            // Head of Property
            ['province' => null, 'role' => 'Head of Property', 'fullname' => 'Al Jay Meliton', 'user_id' => null],

            // Recommending
            ['province' => null, 'role' => 'Recommending', 'fullname' => 'Margie Cabido-Sobretodo', 'user_id' => null],

            // Approving
            ['province' => null, 'role' => 'Approving', 'fullname' => 'Mia Dulce Corazon V. Monesit', 'user_id' => null],
        ];

        // Disable event-based history logging for seeding
        Event::fakeFor(function () use ($officials) {
            foreach ($officials as $official) {
                Official::firstOrCreate(
                    [
                        'province' => $official['province'],
                        'role'     => $official['role'],
                        'fullname' => $official['fullname'],
                    ],
                    [
                        'active'   => true,
                        'user_id'  => $official['user_id'],
                    ]
                );
            }
        });
    }
}
