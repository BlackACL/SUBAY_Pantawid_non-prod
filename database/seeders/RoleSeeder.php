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
        Role::firstOrcreate(['name' => 'superadmin']);
        Role::firstOrcreate(['name' => 'Regional DPSC']);
        Role::firstOrcreate(['name' => 'Provincial DPSC']);
        Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
    }
}
