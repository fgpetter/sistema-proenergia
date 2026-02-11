<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Projeto>
 */
class ProjetoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->sentence(3),
            'colaborador_responsavel_id' => Colaborador::factory()->for(
                User::factory()->role(UserRole::Coordenadores)
            ),
        ];
    }
}
