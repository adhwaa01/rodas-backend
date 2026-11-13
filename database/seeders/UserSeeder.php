<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('SEED_ADMIN_EMAIL');
        $adminPassword = env('SEED_ADMIN_PASSWORD');
        $surveyorEmail = env('SEED_SURVEYOR_EMAIL');
        $surveyorPassword = env('SEED_SURVEYOR_PASSWORD');

        if (
            ! $adminEmail ||
            ! $adminPassword ||
            ! $surveyorEmail ||
            ! $surveyorPassword
        ) {
            throw new \RuntimeException(
                'Set SEED_ADMIN_EMAIL, SEED_ADMIN_PASSWORD, '
                .'SEED_SURVEYOR_EMAIL, and SEED_SURVEYOR_PASSWORD '
                .'in .env before running the user seeder.'
            );
        }

        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $petugasRole = Role::where(
            'name',
            'petugas_surveyor'
        )->firstOrFail();

        // Akun Admin PUPR
        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin PUPR',
                'password' => Hash::make($adminPassword),
                'role_id' => $adminRole->id,
            ]
        );

        // Akun Petugas Surveyor
        User::firstOrCreate(
            ['email' => $surveyorEmail],
            [
                'name' => 'Petugas Surveyor',
                'password' => Hash::make($surveyorPassword),
                'role_id' => $petugasRole->id,
            ]
        );
    }
}
