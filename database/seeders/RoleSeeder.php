<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'superadmin']);
        Role::firstOrCreate(['name' => 'Regional DPSC']);
        Role::firstOrCreate(['name' => 'Provincial DPSC']);
        Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
    }
}
