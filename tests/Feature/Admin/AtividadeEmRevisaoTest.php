<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoProjetoAtividade;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtividadeEmRevisaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_atividade_em_revisao_sai_da_apuracao_de_produtividade(): void
    {
        [$colaborador, $projeto] = $this->criarCenario();
        $this->criarAtividade($projeto, $colaborador, postesProjetados: 100);
        $this->criarAtividade($projeto, $colaborador, postesProjetados: 50, emRevisao: now());

        $linha = app(Colaborador::class)->agregarProdutividade(mesAno: '2026-06')->first();

        $this->assertSame(100, (int) $linha->total_postes_projetados);
        $this->assertSame(100, (int) $linha->total_postes_projeto_cad);
    }

    public function test_projeto_com_somente_atividade_em_revisao_nao_conta_como_projeto(): void
    {
        [$colaborador, $projeto] = $this->criarCenario();
        $this->criarAtividade($projeto, $colaborador, postesProjetados: 100);

        $outroProjeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $projeto->colaborador_responsavel_id,
        ]);
        $this->criarAtividade($outroProjeto, $colaborador, postesProjetados: 80, emRevisao: now());

        $linha = app(Colaborador::class)->agregarProdutividade(mesAno: '2026-06')->first();

        $this->assertSame(1, (int) $linha->total_projetos);
    }

    public function test_atividade_em_revisao_sai_do_relatorio_detalhado(): void
    {
        [$colaborador, $projeto] = $this->criarCenario();
        $atividadeContada = $this->criarAtividade($projeto, $colaborador, postesProjetados: 100);
        $this->criarAtividade($projeto, $colaborador, postesProjetados: 50, emRevisao: now());

        $atividades = app(Atividade::class)->listarAtividadesRelatorio(mesAno: '2026-06');

        $this->assertSame([$atividadeContada->id], $atividades->pluck('id')->all());
    }

    public function test_desmarcar_revisao_devolve_a_atividade_as_metricas(): void
    {
        [$colaborador, $projeto] = $this->criarCenario();
        $this->criarAtividade($projeto, $colaborador, postesProjetados: 100);
        $emRevisao = $this->criarAtividade($projeto, $colaborador, postesProjetados: 50, emRevisao: now());

        $emRevisao->update(['em_revisao' => null]);

        $linha = app(Colaborador::class)->agregarProdutividade(mesAno: '2026-06')->first();

        $this->assertSame(150, (int) $linha->total_postes_projetados);
    }

    public function test_atividade_em_revisao_sai_dos_indicadores_do_painel(): void
    {
        [$colaborador, $projeto] = $this->criarCenario();
        $this->criarAtividade($projeto, $colaborador, postesProjetados: 100);
        $this->criarAtividade($projeto, $colaborador, postesProjetados: 50, emRevisao: now());

        $totais = app(Projeto::class)->totaisGlobaisDashboard('2026-06');
        $ranking = app(Colaborador::class)->producaoDashboard('2026-06');
        $estatisticas = app(Projeto::class)->estatisticasDashboard('2026-06')->get();

        $this->assertSame(100, $totais->totalPostesProjetados);
        $this->assertSame(100, $ranking->first()->total);
        $this->assertSame(100, (int) $estatisticas->first()->total_postes_projetados);
    }

    public function test_atividade_em_revisao_sai_da_evolucao_semanal(): void
    {
        [$colaborador, $projeto] = $this->criarCenario();
        $this->criarAtividade($projeto, $colaborador, postesProjetados: 100);
        $this->criarAtividade($projeto, $colaborador, postesProjetados: 50, emRevisao: now());

        $this->travelTo(Carbon::parse('2026-06-15 12:00:00'));

        $evolucao = app(Atividade::class)->evolucaoSemanalPostes();

        $this->assertSame(100, $evolucao->last()->total);
    }

    /**
     * @return array{0: Colaborador, 1: Projeto}
     */
    private function criarCenario(): array
    {
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->projetista()->create();
        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        return [$colaborador, $projeto];
    }

    private function criarAtividade(
        Projeto $projeto,
        Colaborador $colaborador,
        int $postesProjetados,
        ?Carbon $emRevisao = null,
    ): Atividade {
        return Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => $postesProjetados,
            'data_conclusao' => '2026-06-10',
            'em_revisao' => $emRevisao,
        ]);
    }
}
