<?php

namespace Tests\Feature\Painel;

use App\Enums\TipoProjetoAtividade;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_totais_e_estatisticas_filtram_por_competencia(): void
    {
        $this->criarProjetoComAtividadeCad(
            nome: 'Projeto Junho',
            createdAt: '2026-06-10 10:00:00',
            extensaoProjeto: 80,
            postesProjetados: 15,
            duracaoMinutos: 120,
        );
        $this->criarProjetoComAtividadeCad(
            nome: 'Projeto Julho',
            createdAt: '2026-07-05 10:00:00',
            extensaoProjeto: 50,
            postesProjetados: 5,
            duracaoMinutos: 60,
        );

        $totaisJunho = app(Projeto::class)->totaisGlobaisDashboard('2026-06');
        $this->assertSame(1, $totaisJunho->totalProjetos);
        $this->assertSame(80, $totaisJunho->totalExtensaoProjeto);
        $this->assertSame(15, $totaisJunho->totalPostesProjetados);
        $this->assertSame(7200, $totaisJunho->totalSegundos);
        $this->assertSame(80.0, $totaisJunho->mediaExtensaoPorProjeto);
        $this->assertSame(15.0, $totaisJunho->mediaPostesPorProjeto);
        $this->assertEqualsWithDelta(80 / 15, $totaisJunho->vaoMedioProjetado, 0.001);
        $this->assertSame(['Projeto Junho'], app(Projeto::class)->estatisticasDashboard('2026-06')->pluck('nome')->all());

        $totaisJulho = app(Projeto::class)->totaisGlobaisDashboard('2026-07');
        $this->assertSame(1, $totaisJulho->totalProjetos);
        $this->assertSame(50, $totaisJulho->totalExtensaoProjeto);
        $this->assertSame(5, $totaisJulho->totalPostesProjetados);
        $this->assertSame(3600, $totaisJulho->totalSegundos);
        $this->assertSame(['Projeto Julho'], app(Projeto::class)->estatisticasDashboard('2026-07')->pluck('nome')->all());
    }

    public function test_totais_sem_competencia_somam_todos_os_meses(): void
    {
        $this->criarProjetoComAtividadeCad(
            nome: 'Projeto Junho',
            createdAt: '2026-06-10 10:00:00',
            extensaoProjeto: 80,
            postesProjetados: 15,
            duracaoMinutos: 120,
        );
        $this->criarProjetoComAtividadeCad(
            nome: 'Projeto Julho',
            createdAt: '2026-07-05 10:00:00',
            extensaoProjeto: 50,
            postesProjetados: 5,
            duracaoMinutos: 60,
        );

        $totais = app(Projeto::class)->totaisGlobaisDashboard(null);

        $this->assertSame(2, $totais->totalProjetos);
        $this->assertSame(130, $totais->totalExtensaoProjeto);
        $this->assertSame(20, $totais->totalPostesProjetados);
        $this->assertSame(10800, $totais->totalSegundos);
        $this->assertEqualsCanonicalizing(
            ['Projeto Junho', 'Projeto Julho'],
            app(Projeto::class)->estatisticasDashboard(null)->pluck('nome')->all(),
        );
    }

    public function test_dashboard_competencia_usa_data_conclusao_e_exclui_pendentes(): void
    {
        $colaborador = Colaborador::factory()->create(['nome' => 'Ana Conclusão']);
        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto Criado em Maio',
            'created_at' => '2026-05-10 10:00:00',
            'updated_at' => '2026-05-10 10:00:00',
        ]);

        Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'extensao_projeto' => 75,
            'postes_projetados' => 320,
            'data_conclusao' => '2026-06-30',
        ]);

        Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'extensao_projeto' => 100,
            'postes_projetados' => 100,
            'data_conclusao' => null,
        ]);

        $totaisJunho = app(Projeto::class)->totaisGlobaisDashboard('2026-06');

        $this->assertSame(1, $totaisJunho->totalProjetos);
        $this->assertSame(75, $totaisJunho->totalExtensaoProjeto);
        $this->assertSame(['Projeto Criado em Maio'], app(Projeto::class)->estatisticasDashboard('2026-06')->pluck('nome')->all());
        $this->assertSame(['Ana Conclusão'], app(Colaborador::class)->producaoDashboard('2026-06')->pluck('nome')->all());
        $this->assertSame(0, app(Projeto::class)->totaisGlobaisDashboard('2026-05')->totalProjetos);
    }

    public function test_mes_sem_projeto_retorna_totais_zerados_e_builder_vazio(): void
    {
        $this->criarProjetoComAtividadeCad(
            nome: 'Projeto Junho',
            createdAt: '2026-06-10 10:00:00',
            extensaoProjeto: 80,
            postesProjetados: 15,
            duracaoMinutos: 120,
        );

        $totais = app(Projeto::class)->totaisGlobaisDashboard('2026-08');

        $this->assertSame(0, $totais->totalProjetos);
        $this->assertSame(0, $totais->totalExtensaoProjeto);
        $this->assertSame(0, $totais->totalPostesProjetados);
        $this->assertSame(0, $totais->totalSegundos);
        $this->assertSame(0.0, $totais->mediaExtensaoPorProjeto);
        $this->assertSame(0.0, $totais->mediaPostesPorProjeto);
        $this->assertSame(0.0, $totais->mediaSegundosPorProjeto);
        $this->assertSame(0.0, $totais->vaoMedioProjetado);
        $this->assertSame([], app(Projeto::class)->estatisticasDashboard('2026-08')->pluck('nome')->all());
    }

    public function test_estatisticas_por_projeto_incluem_nome_do_coordenador(): void
    {
        $coordenador = Colaborador::factory()->coordenador()->create([
            'nome' => 'Coordenador Alfa',
        ]);
        $projetista = Colaborador::factory()->projetista()->create();
        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto Com Coordenador',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);
        Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $projetista->id,
            'postes_projetados' => 10,
            'data_conclusao' => '2026-06-10',
        ]);

        $linha = app(Projeto::class)->estatisticasDashboard('2026-06')->first();

        $this->assertSame('Projeto Com Coordenador', $linha->nome);
        $this->assertSame('Coordenador Alfa', $linha->coordenador);
    }

    public function test_atividade_proj_nao_entra_nos_totais_do_resumo(): void
    {
        $colaborador = Colaborador::factory()->create();
        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto Só PROJ',
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        Atividade::factory()->proj()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'extensao_projeto' => 400,
            'postes_projetados' => 40,
            'duracao_minutos' => 300,
        ]);

        $totais = app(Projeto::class)->totaisGlobaisDashboard('2026-06');

        $this->assertSame(0, $totais->totalProjetos);
        $this->assertSame(0, $totais->totalExtensaoProjeto);
        $this->assertSame(0, $totais->totalPostesProjetados);
        $this->assertSame(0, $totais->totalSegundos);
    }

    public function test_projeto_misto_conta_um_e_soma_apenas_parte_cad(): void
    {
        $colaborador = Colaborador::factory()->create();
        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto Misto',
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'extensao_projeto' => 800,
            'postes_projetados' => 80,
            'duracao_minutos' => 600,
            'data_conclusao' => '2026-06-10',
        ]);

        Atividade::factory()->proj()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'extensao_projeto' => 400,
            'postes_projetados' => 40,
            'duracao_minutos' => 300,
            'data_conclusao' => '2026-06-10',
        ]);

        $totais = app(Projeto::class)->totaisGlobaisDashboard('2026-06');

        $this->assertSame(1, $totais->totalProjetos);
        $this->assertSame(800, $totais->totalExtensaoProjeto);
        $this->assertSame(80, $totais->totalPostesProjetados);
        $this->assertSame(36000, $totais->totalSegundos);
    }

    public function test_cenario_conferencia_calcula_medias_e_vao_medio(): void
    {
        $colaborador = Colaborador::factory()->create();

        $misto = Projeto::factory()->create([
            'nome' => 'Projeto Misto',
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);
        Atividade::factory()->cad()->create([
            'projeto_id' => $misto->id,
            'colaborador_id' => $colaborador->id,
            'extensao_projeto' => 800,
            'postes_projetados' => 80,
            'duracao_minutos' => 600,
            'data_conclusao' => '2026-06-10',
        ]);
        Atividade::factory()->proj()->create([
            'projeto_id' => $misto->id,
            'colaborador_id' => $colaborador->id,
            'extensao_projeto' => 400,
            'postes_projetados' => 40,
            'duracao_minutos' => 300,
            'data_conclusao' => '2026-06-10',
        ]);

        $soProj = Projeto::factory()->create([
            'nome' => 'Projeto Só PROJ',
            'created_at' => '2026-06-11 10:00:00',
            'updated_at' => '2026-06-11 10:00:00',
        ]);
        Atividade::factory()->proj()->create([
            'projeto_id' => $soProj->id,
            'colaborador_id' => $colaborador->id,
            'extensao_projeto' => 400,
            'postes_projetados' => 40,
            'duracao_minutos' => 300,
            'data_conclusao' => '2026-06-11',
        ]);

        $soCad = Projeto::factory()->create([
            'nome' => 'Projeto Só CAD',
            'created_at' => '2026-06-12 10:00:00',
            'updated_at' => '2026-06-12 10:00:00',
        ]);
        Atividade::factory()->cad()->create([
            'projeto_id' => $soCad->id,
            'colaborador_id' => $colaborador->id,
            'extensao_projeto' => 400,
            'postes_projetados' => 40,
            'duracao_minutos' => 300,
            'data_conclusao' => '2026-06-12',
        ]);

        $totais = app(Projeto::class)->totaisGlobaisDashboard('2026-06');

        $this->assertSame(2, $totais->totalProjetos);
        $this->assertSame(1200, $totais->totalExtensaoProjeto);
        $this->assertSame(120, $totais->totalPostesProjetados);
        $this->assertSame(54000, $totais->totalSegundos);
        $this->assertSame(600.0, $totais->mediaExtensaoPorProjeto);
        $this->assertSame(60.0, $totais->mediaPostesPorProjeto);
        $this->assertSame(27000.0, $totais->mediaSegundosPorProjeto);
        $this->assertSame(10.0, $totais->vaoMedioProjetado);
    }

    public function test_extensao_desenho_nao_entra_na_extensao_total(): void
    {
        $colaborador = Colaborador::factory()->create();
        $projeto = Projeto::factory()->create([
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'extensao_desenho' => 200,
            'extensao_projeto' => 800,
            'postes_projetados' => 80,
            'duracao_minutos' => 600,
            'data_conclusao' => '2026-06-10',
        ]);

        $totais = app(Projeto::class)->totaisGlobaisDashboard('2026-06');

        $this->assertSame(800, $totais->totalExtensaoProjeto);
    }

    public function test_producao_por_colaborador_soma_cad_e_proj_e_aplica_metas_independentes(): void
    {
        $bruno = Colaborador::factory()->create(['nome' => 'Bruno']);
        $ana = Colaborador::factory()->create(['nome' => 'Ana']);
        $carla = Colaborador::factory()->create(['nome' => 'Carla']);
        $diego = Colaborador::factory()->create(['nome' => 'Diego']);

        $projeto = Projeto::factory()->create([
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $bruno->id,
            'postes_projetados' => 320,
            'data_conclusao' => '2026-06-10',
        ]);
        Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $ana->id,
            'postes_projetados' => 300,
            'data_conclusao' => '2026-06-10',
        ]);
        Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $carla->id,
            'postes_projetados' => 299,
            'data_conclusao' => '2026-06-10',
        ]);
        Atividade::factory()->proj()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $diego->id,
            'postes_projetados' => 500,
            'data_conclusao' => '2026-06-10',
        ]);
        Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $diego->id,
            'postes_projetados' => 0,
            'data_conclusao' => '2026-06-10',
        ]);

        $projetoJulho = Projeto::factory()->create([
            'created_at' => '2026-07-05 10:00:00',
            'updated_at' => '2026-07-05 10:00:00',
        ]);
        Atividade::factory()->cad()->create([
            'projeto_id' => $projetoJulho->id,
            'colaborador_id' => $bruno->id,
            'postes_projetados' => 100,
            'data_conclusao' => '2026-07-05',
        ]);

        $ranking = app(Colaborador::class)->producaoDashboard('2026-06');

        $this->assertSame(['Diego', 'Bruno', 'Ana', 'Carla'], $ranking->pluck('nome')->all());
        $this->assertSame([500, 320, 300, 299], $ranking->pluck('total')->all());
        $this->assertSame([true, true, true, false], $ranking->pluck('acimaDaMeta')->all());
    }

    public function test_producao_por_colaborador_sem_cor_meta_em_todas_competencias(): void
    {
        $colaborador = Colaborador::factory()->create(['nome' => 'Ana']);
        $projeto = Projeto::factory()->create([
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);
        Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'postes_projetados' => 320,
            'data_conclusao' => '2026-06-10',
        ]);

        $ranking = app(Colaborador::class)->producaoDashboard(null);

        $this->assertCount(1, $ranking);
        $this->assertSame(320, $ranking->first()->total);
        $this->assertNull($ranking->first()->acimaDaMeta);
    }

    public function test_evolucao_semanal_soma_cad_e_proj_por_created_at_no_mes_atual(): void
    {
        Carbon::setTestNow('2026-08-20 12:00:00');

        $colaborador = Colaborador::factory()->create();
        $projeto = Projeto::factory()->create([
            'created_at' => '2026-07-15 10:00:00',
            'updated_at' => '2026-07-15 10:00:00',
        ]);

        $semana1 = Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'postes_projetados' => 80,
            'data_conclusao' => '2026-08-03',
        ]);
        $semana1->forceFill(['created_at' => '2026-08-03 10:00:00', 'updated_at' => '2026-08-03 10:00:00'])->save();

        $semana2 = Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'postes_projetados' => 50,
            'data_conclusao' => '2026-08-10',
        ]);
        $semana2->forceFill(['created_at' => '2026-08-10 10:00:00', 'updated_at' => '2026-08-10 10:00:00'])->save();

        $atividadeProj = Atividade::factory()->proj()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'postes_projetados' => 200,
            'data_conclusao' => '2026-08-05',
        ]);
        $atividadeProj->forceFill(['created_at' => '2026-08-05 10:00:00', 'updated_at' => '2026-08-05 10:00:00'])->save();

        $evolucao = app(Atividade::class)->evolucaoSemanalPostes();

        $this->assertSame(['1', '2', '3', '4', '5'], $evolucao->pluck('rotulo')->all());
        $this->assertSame([280, 330, 330, 330, 330], $evolucao->pluck('total')->all());

        Carbon::setTestNow();
    }

    public function test_ranking_e_evolucao_usam_data_de_conclusao(): void
    {
        Carbon::setTestNow('2026-08-20 12:00:00');

        $colaborador = Colaborador::factory()->create(['nome' => 'Ana']);
        $projeto = Projeto::factory()->create([
            'created_at' => '2026-07-20 10:00:00',
            'updated_at' => '2026-07-20 10:00:00',
        ]);

        $atividade = Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'postes_projetados' => 90,
            'data_conclusao' => '2026-08-04',
        ]);
        $atividade->forceFill(['created_at' => '2026-08-04 10:00:00', 'updated_at' => '2026-08-04 10:00:00'])->save();

        $rankingJulho = app(Colaborador::class)->producaoDashboard('2026-07');
        $this->assertTrue($rankingJulho->isEmpty());

        $rankingAgosto = app(Colaborador::class)->producaoDashboard('2026-08');
        $this->assertSame(['Ana'], $rankingAgosto->pluck('nome')->all());
        $this->assertSame([90], $rankingAgosto->pluck('total')->all());

        $evolucao = app(Atividade::class)->evolucaoSemanalPostes();
        $this->assertSame([90, 90, 90, 90, 90], $evolucao->pluck('total')->all());

        Carbon::setTestNow();
    }

    private function criarProjetoComAtividadeCad(
        string $nome,
        string $createdAt,
        int $extensaoProjeto,
        int $postesProjetados,
        int $duracaoMinutos,
    ): void {
        $colaborador = Colaborador::factory()->create();
        $projeto = Projeto::factory()->create([
            'nome' => $nome,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        Atividade::factory()->cad()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'extensao_desenho' => 0,
            'extensao_projeto' => $extensaoProjeto,
            'postes_desenhados' => 0,
            'postes_projetados' => $postesProjetados,
            'duracao_minutos' => $duracaoMinutos,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'data_conclusao' => Carbon::parse($createdAt)->toDateString(),
        ]);
    }
}
