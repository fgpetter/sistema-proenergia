<?php

namespace Database\Seeders;

use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Support\BonusColaboradorCalculator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Dados fixos para conferir Dashboard e Análise gráfica.
 *
 * Projetistas (mês atual / Competência):
 * - Valentine N.: 450 Projeto CAD (acima da meta 300)
 * - Gerson A.: 320 Projeto CAD (acima da meta 300)
 * - João P.: 180 Projeto CAD (abaixo da meta 300)
 *
 * Cards do resumo executivo (mês atual, só CAD):
 * - Total de projetos: 3
 * - Extensão total: 9500
 * - Postes projetados: 950
 * - Total de horas: 23h 45min (1425 min)
 * - Extensão por projeto: 3166,7
 * - Postes por projeto: 316,7
 * - Horas por projeto: 7h 55min
 * - Vão médio projetado: 10,0
 *
 * Evolução semanal acumulada (atividades.created_at no mês atual):
 * - Semana 1 (1–7): 450
 * - Semana 2 (1–14): 770
 * - Semana 3 (1–21): 950
 * - Semanas 4–5 (1–28 / 1–fim): 950
 */
class DashboardConferenciaSeeder extends Seeder
{
    public function run(): void
    {
        $coordenador = Colaborador::factory()->coordenador()->create([
            'nome' => 'Coordenador Conferência',
        ]);

        $valentine = Colaborador::factory()->projetista()->create([
            'nome' => 'Valentine N.',
            'remuneracao' => 500000,
        ]);
        $gerson = Colaborador::factory()->projetista()->create([
            'nome' => 'Gerson A.',
            'remuneracao' => 500000,
        ]);
        $joao = Colaborador::factory()->projetista()->create([
            'nome' => 'João P.',
            'remuneracao' => 500000,
        ]);

        $mesAtual = now()->copy()->startOfMonth()->addDays(9)->setTime(10, 0);
        $mesAnterior = now()->copy()->subMonth()->startOfMonth()->addDays(5)->setTime(10, 0);

        $this->criarProjetoCad(
            coordenador: $coordenador,
            projetista: $valentine,
            nomeProjeto: 'Projeto CAD Valentine',
            postesProjetados: 450,
            projetoCreatedAt: $mesAtual,
            atividadeCreatedAt: now()->copy()->startOfMonth()->addDays(2)->setTime(10, 0),
        );
        $this->criarProjetoCad(
            coordenador: $coordenador,
            projetista: $gerson,
            nomeProjeto: 'Projeto CAD Gerson',
            postesProjetados: 320,
            projetoCreatedAt: $mesAtual->copy()->addDay(),
            atividadeCreatedAt: now()->copy()->startOfMonth()->addDays(9)->setTime(10, 0),
        );
        $this->criarProjetoCad(
            coordenador: $coordenador,
            projetista: $joao,
            nomeProjeto: 'Projeto CAD João',
            postesProjetados: 180,
            projetoCreatedAt: $mesAtual->copy()->addDays(2),
            atividadeCreatedAt: now()->copy()->startOfMonth()->addDays(16)->setTime(10, 0),
        );

        $this->criarProjetoMesAnterior($coordenador, $valentine, $mesAnterior);
    }

    private function criarProjetoCad(
        Colaborador $coordenador,
        Colaborador $projetista,
        string $nomeProjeto,
        int $postesProjetados,
        Carbon $projetoCreatedAt,
        Carbon $atividadeCreatedAt,
    ): void {
        $projeto = Projeto::factory()->create([
            'nome' => $nomeProjeto,
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => $projetoCreatedAt,
            'updated_at' => $projetoCreatedAt,
        ]);

        $state = $postesProjetados >= BonusColaboradorCalculator::LIMITE_POSTES_PROJETO_CAD
            ? 'projetoCadAcimaDaMeta'
            : 'projetoCadAbaixoDaMeta';

        $atividade = Atividade::factory()
            ->{$state}($postesProjetados)
            ->create([
                'projeto_id' => $projeto->id,
                'colaborador_id' => $projetista->id,
                'nome' => 'Atividade CAD '.$projetista->nome,
                'extensao_projeto' => $postesProjetados * 10,
                'duracao_minutos' => (int) round($postesProjetados * 1.5),
            ]);

        $atividade->forceFill([
            'created_at' => $atividadeCreatedAt,
            'updated_at' => $atividadeCreatedAt,
        ])->save();
    }

    private function criarProjetoMesAnterior(Colaborador $coordenador, Colaborador $projetista, Carbon $createdAt): void
    {
        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto Mês Anterior',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        Atividade::factory()->projetoCadAbaixoDaMeta(30)->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $projetista->id,
            'nome' => 'Atividade CAD Mês Anterior',
            'extensao_projeto' => 300,
            'duracao_minutos' => 180,
        ]);
    }
}
