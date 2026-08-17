<?php

namespace Tests\Feature\Painel;

use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Queries\DashboardMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_totais_e_estatisticas_filtram_por_competencia(): void
    {
        $this->criarProjetoComAtividade(
            nome: 'Projeto Junho',
            createdAt: '2026-06-10 10:00:00',
            extensaoProjeto: 80,
            postesProjetados: 15,
            inicio: '2026-06-11 08:00:00',
            fim: '2026-06-11 10:00:00',
        );
        $this->criarProjetoComAtividade(
            nome: 'Projeto Julho',
            createdAt: '2026-07-05 10:00:00',
            extensaoProjeto: 50,
            postesProjetados: 5,
            inicio: '2026-07-06 08:00:00',
            fim: '2026-07-06 09:00:00',
        );

        $metrics = app(DashboardMetrics::class);

        $totaisJunho = $metrics->totaisGlobais('2026-06');
        $this->assertSame(1, $totaisJunho->totalProjetos);
        $this->assertSame(80, $totaisJunho->totalExtensaoProjeto);
        $this->assertSame(15, $totaisJunho->totalPostesProjetados);
        $this->assertSame(7200, $totaisJunho->totalSegundos);
        $this->assertSame(['Projeto Junho'], $metrics->estatisticasPorProjeto('2026-06')->pluck('nome')->all());

        $totaisJulho = $metrics->totaisGlobais('2026-07');
        $this->assertSame(1, $totaisJulho->totalProjetos);
        $this->assertSame(50, $totaisJulho->totalExtensaoProjeto);
        $this->assertSame(5, $totaisJulho->totalPostesProjetados);
        $this->assertSame(3600, $totaisJulho->totalSegundos);
        $this->assertSame(['Projeto Julho'], $metrics->estatisticasPorProjeto('2026-07')->pluck('nome')->all());
    }

    public function test_totais_sem_competencia_somam_todos_os_meses(): void
    {
        $this->criarProjetoComAtividade(
            nome: 'Projeto Junho',
            createdAt: '2026-06-10 10:00:00',
            extensaoProjeto: 80,
            postesProjetados: 15,
            inicio: '2026-06-11 08:00:00',
            fim: '2026-06-11 10:00:00',
        );
        $this->criarProjetoComAtividade(
            nome: 'Projeto Julho',
            createdAt: '2026-07-05 10:00:00',
            extensaoProjeto: 50,
            postesProjetados: 5,
            inicio: '2026-07-06 08:00:00',
            fim: '2026-07-06 09:00:00',
        );

        $metrics = app(DashboardMetrics::class);
        $totais = $metrics->totaisGlobais(null);

        $this->assertSame(2, $totais->totalProjetos);
        $this->assertSame(130, $totais->totalExtensaoProjeto);
        $this->assertSame(20, $totais->totalPostesProjetados);
        $this->assertSame(10800, $totais->totalSegundos);
        $this->assertEqualsCanonicalizing(
            ['Projeto Junho', 'Projeto Julho'],
            $metrics->estatisticasPorProjeto(null)->pluck('nome')->all(),
        );
    }

    public function test_mes_sem_projeto_retorna_totais_zerados_e_builder_vazio(): void
    {
        $this->criarProjetoComAtividade(
            nome: 'Projeto Junho',
            createdAt: '2026-06-10 10:00:00',
            extensaoProjeto: 80,
            postesProjetados: 15,
            inicio: '2026-06-11 08:00:00',
            fim: '2026-06-11 10:00:00',
        );

        $metrics = app(DashboardMetrics::class);
        $totais = $metrics->totaisGlobais('2026-08');

        $this->assertSame(0, $totais->totalProjetos);
        $this->assertSame(0, $totais->totalExtensaoProjeto);
        $this->assertSame(0, $totais->totalPostesProjetados);
        $this->assertSame(0, $totais->totalSegundos);
        $this->assertSame([], $metrics->estatisticasPorProjeto('2026-08')->pluck('nome')->all());
    }

    private function criarProjetoComAtividade(
        string $nome,
        string $createdAt,
        int $extensaoProjeto,
        int $postesProjetados,
        string $inicio,
        string $fim,
    ): void {
        $colaborador = Colaborador::factory()->create();
        $projeto = Projeto::factory()->create([
            'nome' => $nome,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'extensao_desenho' => 0,
            'extensao_projeto' => $extensaoProjeto,
            'postes_desenhados' => 0,
            'postes_projetados' => $postesProjetados,
            'data_hora_inicio' => $inicio,
            'data_hora_fim' => $fim,
        ]);
    }
}
