<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Place;
use Illuminate\Support\Facades\DB;

class PlacesSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Place::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Province => Municipalities => Offices mapping
        $data = [
            'DAVAO CITY' => [
                'Davao City' => [
                    'Paquibato Sub-District',
                    'Talomo A Sub-District',
                    'Talomo B Sub-District',
                    'Toril A Sub-District',
                    'Toril B Sub-District',
                    'Buhangin A Sub-District',
                    'Buhangin B Sub-District',
                    'Poblacion Sub-District',
                    'Agdao Sub-District',
                    'Bunawan Sub-District',
                    'Calinan Sub-District',
                    'Baguio Sub-District',
                    'Tugbok Sub-District',
                    'Marilog Sub-District'
                ]
            ],
            'DAVAO DE ORO' => [
                'MONKAYO' => ['Monkayo Municipal Operations Office'],
                'COMPOSTELA' => ['Compostela Municipal Operation Office'],
                'MONTEVISTA' => ['Montevista Municipal Operations Office'],
                'NEW BATAAN' => ['New Bataan Municipal Operations Office'],
                'MARAGUSAN (SAN MARIANO)' => ['Maragusan Municipal Operations Office'],
                'NABUNTURAN (Capital)' => ['Nabunturan Municipal Operations Office'],
                'MAWAB' => ['Mawab Municipal Operations Office'],
                'MACO' => ['Maco Municipal Operations Office'],
                'PANTUKAN' => ['Pantukan Municipal Operations Office'],
                'MABINI (DOÑA ALICIA)' => ['Mabini Municipal Operations Office'],
                'LAAK (SAN VICENTE)' => ['Laak Municipal Operations Office']
            ],
            'DAVAO DEL NORTE' => [
                'ASUNCION (SAUG)' => ['Asuncion Municipal Operations Office'],
                'BRAULIO E. DUJALI' => ['Braulio E. Dujali Municipal Operations Office'],
                'CARMEN' => ['Carmen Municipal Operations Office'],
                'KAPALONG' => ['Kapalong Municipal Operations Office'],
                'NEW CORELLA' => ['New Corella Municipal Operations Office'],
                'SAN ISIDRO' => ['San Isidro Municipal Operations Office'],
                'SANTO TOMAS' => ['Santo Tomas Municipal Operations Office'],
                'TALAINGOD' => ['Talaingod Municipal Operations Office'],
                'CITY OF TAGUM (Capital)' => ['Tagum City Operations Office'],
                'CITY OF PANABO' => ['Panabo City Operations Office'],
                'ISLAND GARDEN CITY OF SAMAL' => ['Island Garden City of Samal City Operations Office']
            ],
            'DAVAO DEL SUR' => [
                'BANSALAN' => ['Bansalan Municipal Operations Office'],
                'HAGONOY' => ['Hagonoy Municipal Operations Office'],
                'KIBLAWAN' => ['Kiblawan Municipal Operations Office'],
                'MAGSAYSAY' => ['Magsaysay Municipal Operations Office'],
                'MALALAG' => ['Malalag Municipal Operations Office'],
                'MATANAO' => ['Matanao Municipal Operations Office'],
                'PADADA' => ['Padada Municipal Operations Office'],
                'SANTA CRUZ' => ['Sta. Cruz Municipal Operations Office'],
                'CITY OF DIGOS (Capital)' => ['Digos City Operations Office'],
                'SULOP' => ['Sulop Municipal Operations Office']
            ],
            'DAVAO OCCIDENTAL' => [
                'DON MARCELINO' => ['Don Marcelino Municipal Operations Office'],
                'JOSE ABAD SANTOS (TRINIDAD)' => ['Jose Abad Santos Municipal Operations Office'],
                'MALITA' => ['Malita Municipal Operations Office'],
                'SANTA MARIA' => ['Santa Maria Municipal Operations Office'],
                'SARANGANI' => ['Sarangani Municipal Operations Office']
            ],
            'DAVAO ORIENTAL' => [
                'BAGANGA' => ['Baganga Municipal Operations Office'],
                'BANAYBANAY' => ['Banaybanay Municipal Operations Office'],
                'BOSTON' => ['Boston Municipal Operations Office'],
                'CARAGA' => ['Caraga Municipal Operations Office'],
                'CATEEL' => ['Cateel Municipal Operations Office'],
                'GOVERNOR GENEROSO' => ['Governor Generoso Municipal Operations Office'],
                'LUPON' => ['Lupon Municipal Operations Office'],
                'MANAY' => ['Manay Municipal Operations Office'],
                'CITY OF MATI (Capital)' => ['Mati City Operations Office'],
                'SAN ISIDRO' => ['San Isidro Municipal Operations Office'],
                'TARRAGONA' => ['Tarragona Municipal Operations Office']
            ]
        ];

        $this->command->info('Seeding places...');
        $provinceCount = 0;
        $municipalityCount = 0;
        $officeCount = 0;

        foreach ($data as $provinceName => $municipalities) {
            // Create Province
            $province = Place::create([
                'type' => 'province',
                'name' => $provinceName,
                'parent_id' => null
            ]);
            $provinceCount++;
            $this->command->info("✓ Province: {$provinceName}");

            foreach ($municipalities as $municipalityName => $offices) {
                // Create Municipality
                $municipality = Place::create([
                    'type' => 'municipality',
                    'name' => $municipalityName,
                    'parent_id' => $province->id
                ]);
                $municipalityCount++;
                $this->command->info("  ✓ Municipality: {$municipalityName}");

                foreach ($offices as $officeName) {
                    // Create Office
                    Place::create([
                        'type' => 'office',
                        'name' => $officeName,
                        'parent_id' => $municipality->id
                    ]);
                    $officeCount++;
                    $this->command->info("    ✓ Office: {$officeName}");
                }
            }
        }

        $this->command->info("\n=== Seeding Complete ===");
        $this->command->info("Provinces: {$provinceCount}");
        $this->command->info("Municipalities: {$municipalityCount}");
        $this->command->info("Offices: {$officeCount}");
        $this->command->info("Total: " . ($provinceCount + $municipalityCount + $officeCount));
    }
}
