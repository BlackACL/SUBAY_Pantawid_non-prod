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
            ['email' => 'main.central321@gmail.com'],
            [
                'fullname' => 'Superadmin',
                'username' => 'Superadmin',
                'company_id' => 'ADMIN-001',
                'email_verified_at' => now(),
                'password' => Hash::make('Superadmin@123'),
                'employee_status' => 'Regular',
                'office' => 'Regional Office',
                'region' => 'Region XI',
                'province' => 'Davao Del Sur',
                'municipality' => 'Davao City',
                'access_level' => 'Superadmin',
                'activated' => 'Yes',
                'locked_status' => 'No',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $superadmin->assignRole('superadmin');
    }
}
