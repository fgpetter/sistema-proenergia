<?php

namespace Database\Factories;

use App\Enums\TipoProjetoAtividade;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Support\BonusColaboradorCalculator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Atividade>
 */
class AtividadeFactory extends Factory
{
    protected $model = Atividade::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'projeto_id' => Projeto::factory(),
            'nome' => 'Atividade '.random_int(1, 100),
            'colaborador_id' => Colaborador::factory(),
            'extensao_desenho' => 0,
            'extensao_projeto' => 0,
            'postes_desenhados' => 0,
            'postes_projetados' => 0,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'duracao_minutos' => 0,
        ];
    }

    public function cad(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tipo_projeto' => TipoProjetoAtividade::Cad,
        ]);
    }

    public function proj(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tipo_projeto' => TipoProjetoAtividade::Proj,
        ]);
    }

    /**
     * Projeto CAD com postes acima ou iguais à Meta Projeto CAD (300).
     */
    public function projetoCadAcimaDaMeta(?int $postesProjetados = null): static
    {
        $minimo = BonusColaboradorCalculator::LIMITE_POSTES_PROJETO_CAD;

        return $this->cad()->state(fn (array $attributes): array => [
            'postes_projetados' => $postesProjetados ?? fake()->numberBetween($minimo, $minimo + 200),
        ]);
    }

    /**
     * Projeto CAD com postes abaixo da Meta Projeto CAD (300).
     */
    public function projetoCadAbaixoDaMeta(?int $postesProjetados = null): static
    {
        $maximo = BonusColaboradorCalculator::LIMITE_POSTES_PROJETO_CAD - 1;

        return $this->cad()->state(fn (array $attributes): array => [
            'postes_projetados' => $postesProjetados ?? fake()->numberBetween(1, $maximo),
        ]);
    }
}
