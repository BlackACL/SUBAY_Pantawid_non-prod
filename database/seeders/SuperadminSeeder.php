<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'full_name' => 'Superadmin',
                'username' => 'superadmin',
                'email_verified_at' => now(),
                'password' => Hash::make('superadmin'),
                'status' => 'REGULAR',
                'region' => 'Region XI',
                'office_id' => 1, // make sure office ID 1 exists
                'area_province' => 'Davao del Sur',
                'area_municipality' => 'Davao City',
                'contact_number' => '09123456789',
                'position' => 'Admin',
                'id_number' => 'EMP-001',
                'role_id' => 1, // make sure role ID 1 exists and is superadmin
                'activated' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $superadmin->assignRole('superadmin');
    }
}
