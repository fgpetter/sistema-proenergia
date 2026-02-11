<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@proenergia.com',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
        ]);

        Colaborador::factory()->count(10)->create();
        Colaborador::factory()->coordenador()->count(2)->create();
        Colaborador::factory()->administrativo()->count(2)->create();
    }
}
