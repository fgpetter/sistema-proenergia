<?php

namespace Tests\Unit;

use App\Enums\TipoProjetoParte;
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

    public function test_retorna_zero_no_limite_exato_cad(): void
    {
        $bonus = $this->calculator->calcular(
            postesProjetadosCad: 300,
            postesProjetadosProj: 0,
        );

        $this->assertSame(0.0, $bonus);
    }

    public function test_retorna_zero_no_limite_exato_proj(): void
    {
        $bonus = $this->calculator->calcular(
            postesProjetadosCad: 0,
            postesProjetadosProj: 230,
        );

        $this->assertSame(0.0, $bonus);
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

        // max(0, 500-300) * 1.82 = 200 * 1.82 = 364
        $this->assertEqualsWithDelta(364.0, $bonusPositivo, 0.0001);
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

        // max(0, 250-230) * 1.82 = 20 * 1.82 = 36.4
        $this->assertEqualsWithDelta(36.4, $bonusPositivo, 0.0001);
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

    public function test_calcula_de_partes_agregando_cad_e_proj(): void
    {
        $bonus = $this->calculator->calcularDePartes([
            [
                'tipo_projeto' => TipoProjetoParte::Cad,
                'postes_desenhados' => 150,
                'postes_projetados' => 100,
            ],
            [
                'tipo_projeto' => TipoProjetoParte::Proj,
                'postes_desenhados' => 150,
                'postes_projetados' => 80,
            ],
        ]);

        $this->assertSame(0.0, $bonus);

        $bonusMisto = $this->calculator->calcularDePartes([
            [
                'tipo_projeto' => TipoProjetoParte::Cad,
                'postes_projetados' => 450,
            ],
            [
                'tipo_projeto' => TipoProjetoParte::Proj,
                'postes_projetados' => 250,
            ],
        ]);

        // (450-300) + (250-230) = 170 → 170 * 1.82 = 309.4
        $this->assertEqualsWithDelta(309.4, $bonusMisto, 0.0001);
    }

    public function test_soma_bonus_por_colaborador_sem_separar_projetos(): void
    {
        $bonusPorColaborador = $this->calculator->somarPorColaborador(new Collection([
            (object) [
                'colaborador_id' => 1,
                'projeto_id' => 10,
                'tipo_projeto' => TipoProjetoParte::Cad->value,
                'postes_desenhados' => 140,
                'postes_projetados' => 250,
            ],
            (object) [
                'colaborador_id' => 1,
                'projeto_id' => 20,
                'tipo_projeto' => TipoProjetoParte::Cad->value,
                'postes_desenhados' => 140,
                'postes_projetados' => 250,
            ],
            (object) [
                'colaborador_id' => 2,
                'projeto_id' => 10,
                'tipo_projeto' => TipoProjetoParte::Cad->value,
                'postes_desenhados' => 50,
                'postes_projetados' => 10,
            ],
        ]));

        // colaborador 1: (500-300)=200 → 364
        $this->assertEqualsWithDelta(364.0, $bonusPorColaborador[1], 0.0001);
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
}
