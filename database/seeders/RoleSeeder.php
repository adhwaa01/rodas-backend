<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Admin PUPR']
        );

        Role::firstOrCreate(
            ['name' => 'petugas_surveyor'],
            ['display_name' => 'Petugas Surveyor']
        );
    }
}
