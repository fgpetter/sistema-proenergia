<?php

namespace Tests\Unit;

use App\Enums\TipoProjetoAtividade;
use App\Models\Colaborador;
use App\Support\BonusColaboradorCalculator;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class BonusColaboradorCalculatorTest extends TestCase
{
    private BonusColaboradorCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new BonusColaboradorCalculator;
    }

    public function test_retorna_zero_quando_abaixo_dos_limites(): void
    {
        $bonus = $this->calculator->calcular(
            postesDesenhados: 100,
            postesProjetadosCad: 50,
            postesProjetadosProj: 0,
        );

        $this->assertSame(0.0, $bonus);
    }

    public function test_aplica_bonus_fixo_no_limite_exato_cad(): void
    {
        $bonus = $this->calculator->calcular(
            postesProjetadosCad: 300,
            postesProjetadosProj: 0,
        );

        $this->assertSame(300.0, $bonus);
    }

    public function test_aplica_bonus_fixo_no_limite_exato_proj(): void
    {
        $bonus = $this->calculator->calcular(
            postesProjetadosCad: 0,
            postesProjetadosProj: 230,
        );

        $this->assertSame(300.0, $bonus);
    }

    public function test_bonus_fixo_uma_vez_quando_ambas_metas_atingidas(): void
    {
        $bonus = $this->calculator->calcular(
            postesProjetadosCad: 300,
            postesProjetadosProj: 230,
        );

        $this->assertSame(300.0, $bonus);
    }

    public function test_aplica_formula_com_postes_cad(): void
    {
        $bonus = $this->calculator->calcular(
            postesDesenhados: 300,
            postesProjetadosCad: 100,
            postesProjetadosProj: 0,
        );

        $this->assertSame(0.0, $bonus);

        $bonusPositivo = $this->calculator->calcular(
            postesDesenhados: 280,
            postesProjetadosCad: 500,
            postesProjetadosProj: 0,
        );

        // 300 + max(0, 500-300) * 2 = 300 + 400 = 700
        $this->assertEqualsWithDelta(700.0, $bonusPositivo, 0.0001);
    }

    public function test_aplica_formula_com_postes_proj(): void
    {
        $bonus = $this->calculator->calcular(
            postesDesenhados: 280,
            postesProjetadosCad: 0,
            postesProjetadosProj: 200,
        );

        $this->assertSame(0.0, $bonus);

        $bonusPositivo = $this->calculator->calcular(
            postesProjetadosCad: 0,
            postesProjetadosProj: 250,
        );

        // 300 + max(0, 250-230) * 2 = 300 + 40 = 340
        $this->assertEqualsWithDelta(340.0, $bonusPositivo, 0.0001);
    }

    public function test_ignora_postes_desenhados_no_calculo(): void
    {
        $bonus = $this->calculator->calcular(
            postesDesenhados: 500,
            postesProjetadosCad: 100,
            postesProjetadosProj: 0,
        );

        $this->assertSame(0.0, $bonus);
    }

    public function test_calcula_de_atividades_agregando_cad_e_proj(): void
    {
        $bonus = $this->calculator->calcularDeAtividades([
            [
                'tipo_projeto' => TipoProjetoAtividade::Cad,
                'postes_desenhados' => 150,
                'postes_projetados' => 100,
            ],
            [
                'tipo_projeto' => TipoProjetoAtividade::Proj,
                'postes_desenhados' => 150,
                'postes_projetados' => 80,
            ],
        ]);

        $this->assertSame(0.0, $bonus);

        $bonusMisto = $this->calculator->calcularDeAtividades([
            [
                'tipo_projeto' => TipoProjetoAtividade::Cad,
                'postes_projetados' => 450,
            ],
            [
                'tipo_projeto' => TipoProjetoAtividade::Proj,
                'postes_projetados' => 250,
            ],
        ]);

        // 300 + (450-300) + (250-230) = 300 + 170 → 170 * 2 = 340 → 640
        $this->assertEqualsWithDelta(640.0, $bonusMisto, 0.0001);
    }

    public function test_soma_bonus_por_colaborador_sem_separar_projetos(): void
    {
        $bonusPorColaborador = $this->calculator->somarPorColaborador(new Collection([
            (object) [
                'colaborador_id' => 1,
                'projeto_id' => 10,
                'tipo_projeto' => TipoProjetoAtividade::Cad->value,
                'postes_desenhados' => 140,
                'postes_projetados' => 250,
            ],
            (object) [
                'colaborador_id' => 1,
                'projeto_id' => 20,
                'tipo_projeto' => TipoProjetoAtividade::Cad->value,
                'postes_desenhados' => 140,
                'postes_projetados' => 250,
            ],
            (object) [
                'colaborador_id' => 2,
                'projeto_id' => 10,
                'tipo_projeto' => TipoProjetoAtividade::Cad->value,
                'postes_desenhados' => 50,
                'postes_projetados' => 10,
            ],
        ]));

        // colaborador 1: 300 + (500-300)*2 = 700
        $this->assertEqualsWithDelta(700.0, $bonusPorColaborador[1], 0.0001);
        $this->assertSame(0.0, $bonusPorColaborador[2]);
    }

    public function test_formatar_meta_exibe_postes_por_tipo_em_relacao_aos_limites(): void
    {
        $this->assertSame('500 / 300', $this->calculator->formatarMetaCad(500));
        $this->assertSame('300 / 230', $this->calculator->formatarMetaProj(300));
        $this->assertSame(
            '500 / 300 - 300 / 230',
            $this->calculator->formatarMeta(
                postesProjetadosCad: 500,
                postesProjetadosProj: 300,
            ),
        );
    }

    public function test_aplica_teto_de_setenta_por_cento_da_remuneracao(): void
    {
        // remuneração R$ 5.000,00 = 500000 centavos → teto 3.500,00
        $bonusReal = $this->calculator->aplicarTeto(4000.0, 500000);

        $this->assertEqualsWithDelta(3500.0, $bonusReal, 0.0001);
    }

    public function test_nao_altera_bonus_quando_abaixo_do_teto(): void
    {
        $bonusReal = $this->calculator->aplicarTeto(700.0, 500000);

        $this->assertEqualsWithDelta(700.0, $bonusReal, 0.0001);
    }

    public function test_zera_bonus_quando_remuneracao_ausente(): void
    {
        $this->assertSame(0.0, $this->calculator->aplicarTeto(2000.0, null));
    }

    public function test_enriquece_colaborador_com_meta_e_bonus_com_teto(): void
    {
        $colaborador = new Colaborador;
        $colaborador->remuneracao = 500000;
        $colaborador->total_postes_projetados_cad = 500;
        $colaborador->total_postes_projetados_proj = 0;

        $this->calculator->enriquecerColaborador($colaborador);

        $this->assertSame('500 / 300', $colaborador->meta_cad);
        $this->assertSame('0 / 230', $colaborador->meta_proj);
        $this->assertSame(500, $colaborador->total_postes);
        $this->assertEqualsWithDelta(700.0, $colaborador->total_bonus, 0.0001);
    }
}
