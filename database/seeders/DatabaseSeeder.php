<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Super Admin
        User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@proenergia.com',
        ]);

        // Admin
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@proenergia.com',
        ]);

        // Gestores
        User::factory()->gestor()->count(3)->create();

        // Prestadores
        User::factory()->prestador()->count(5)->create();
    }
}
