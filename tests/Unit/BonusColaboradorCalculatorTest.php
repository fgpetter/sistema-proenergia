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

    public function test_aplica_formula_com_postes_cad(): void
    {
        $bonus = $this->calculator->calcular(
            postesDesenhados: 300,
            postesProjetadosCad: 100,
            postesProjetadosProj: 0,
        );

        // max(0, (300-280)+(100-220)) * 1.82 = max(0, -100) * 1.82 = 0
        $this->assertSame(0.0, $bonus);

        $bonusPositivo = $this->calculator->calcular(
            postesDesenhados: 280,
            postesProjetadosCad: 320,
            postesProjetadosProj: 0,
        );

        // max(0, (280-280)+(320-220)) * 1.82 = 100 * 1.82 = 182
        $this->assertEqualsWithDelta(182.0, $bonusPositivo, 0.0001);
    }

    public function test_aplica_multiplicador_proj_e_bonus(): void
    {
        $bonus = $this->calculator->calcular(
            postesDesenhados: 280,
            postesProjetadosCad: 0,
            postesProjetadosProj: 200,
        );

        // max(0, 0 + (200 * 1.375 - 220)) * 1.82 = 55 * 1.82 = 100.1
        $this->assertEqualsWithDelta(100.1, $bonus, 0.0001);
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

        // postesDesenhados=300, cad=100, proj=80
        // max(0, (300-280)+(100 + 80*1.375 - 220)) * 1.82 = 10 * 1.82 = 18.2
        $this->assertEqualsWithDelta(18.2, $bonus, 0.0001);
    }

    public function test_soma_bonus_por_colaborador_sem_separar_projetos(): void
    {
        $bonusPorColaborador = $this->calculator->somarPorColaborador(new Collection([
            (object) [
                'colaborador_id' => 1,
                'projeto_id' => 10,
                'tipo_projeto' => TipoProjetoParte::Cad->value,
                'postes_desenhados' => 140,
                'postes_projetados' => 160,
            ],
            (object) [
                'colaborador_id' => 1,
                'projeto_id' => 20,
                'tipo_projeto' => TipoProjetoParte::Cad->value,
                'postes_desenhados' => 140,
                'postes_projetados' => 160,
            ],
            (object) [
                'colaborador_id' => 2,
                'projeto_id' => 10,
                'tipo_projeto' => TipoProjetoParte::Cad->value,
                'postes_desenhados' => 50,
                'postes_projetados' => 10,
            ],
        ]));

        // colaborador 1: (280-280)+(320-220)=100 → 182
        $this->assertEqualsWithDelta(182.0, $bonusPorColaborador[1], 0.0001);
        $this->assertSame(0.0, $bonusPorColaborador[2]);
    }
}
