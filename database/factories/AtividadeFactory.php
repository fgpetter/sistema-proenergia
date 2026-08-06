<?php

namespace Database\Factories;

use App\Enums\TipoProjetoAtividade;
use App\Enums\UserRole;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Atividade>
 */
class AtividadeFactory extends Factory
{
    protected $model = Atividade::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = $this->faker->randomElement([
            UserRole::Levantadores,
            UserRole::Orcamentistas,
            UserRole::Projetistas,
        ]);

        return [
            'projeto_id' => Projeto::factory(),
            'nome' => 'Atividade '.random_int(1, 100),
            'colaborador_id' => Colaborador::factory()->for(User::factory()->role($role)),
            'extensao_desenho' => random_int(100, 500),
            'extensao_projeto' => random_int(100, 500),
            'postes_desenhados' => random_int(10, 50),
            'postes_projetados' => random_int(10, 50),
            'tipo_projeto' => $this->faker->randomElement(TipoProjetoAtividade::cases()),
        ];
    }
}
