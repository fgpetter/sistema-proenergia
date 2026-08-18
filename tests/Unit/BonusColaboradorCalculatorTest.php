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
            postesProjetoCad: 299,
            postesProjetoProj: 229,
        );

        $this->assertSame(0.0, $bonus);
    }

    public function test_aplica_bonus_fixo_no_limite_exato_projeto_cad(): void
    {
        $bonus = $this->calculator->calcular(
            postesProjetoCad: 300,
        );

        $this->assertSame(300.0, $bonus);
    }

    public function test_aplica_formula_com_um_poste_extra_projeto_cad(): void
    {
        $bonus = $this->calculator->calcular(
            postesProjetoCad: 301,
        );

        $this->assertEqualsWithDelta(302.0, $bonus, 0.0001);
    }

    public function test_bonus_fixo_uma_vez_quando_projeto_cad_e_proj_no_limite(): void
    {
        $bonus = $this->calculator->calcular(
            postesProjetoCad: 300,
            postesProjetoProj: 230,
        );

        $this->assertSame(300.0, $bonus);
    }

    public function test_soma_excedentes_de_projeto_cad_e_proj(): void
    {
        $bonus = $this->calculator->calcular(
            postesProjetoCad: 500,
            postesProjetoProj: 300,
        );

        $this->assertEqualsWithDelta(840.0, $bonus, 0.0001);
    }

    public function test_aplica_bonus_fixo_no_limite_exato_desenho_cad(): void
    {
        $bonus = $this->calculator->calcular(
            postesDesenhoCad: 400,
        );

        $this->assertSame(300.0, $bonus);
    }

    public function test_bonus_fixo_uma_vez_quando_as_tres_metas_no_limite(): void
    {
        $bonus = $this->calculator->calcular(
            postesDesenhoCad: 400,
            postesProjetoCad: 300,
            postesProjetoProj: 230,
        );

        $this->assertSame(300.0, $bonus);
    }

    public function test_soma_excedentes_das_tres_categorias(): void
    {
        $bonus = $this->calculator->calcular(
            postesDesenhoCad: 500,
            postesProjetoCad: 500,
            postesProjetoProj: 300,
        );

        $this->assertEqualsWithDelta(1040.0, $bonus, 0.0001);
    }

    public function test_nao_soma_desenho_cad_com_projeto_cad(): void
    {
        $bonus = $this->calculator->calcularDeAtividades([
            [
                'tipo_projeto' => TipoProjetoAtividade::Cad,
                'postes_desenhados' => 200,
                'postes_projetados' => 200,
            ],
        ]);

        $this->assertSame(0.0, $bonus);
    }

    public function test_ignora_postes_desenhados_em_atividade_proj(): void
    {
        $bonus = $this->calculator->calcularDeAtividades([
            [
                'tipo_projeto' => TipoProjetoAtividade::Proj,
                'postes_desenhados' => 400,
                'postes_projetados' => 100,
            ],
        ]);

        $this->assertSame(0.0, $bonus);
    }

    public function test_calcula_de_atividades_agregando_tres_categorias(): void
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
                'postes_desenhados' => 450,
                'postes_projetados' => 450,
            ],
            [
                'tipo_projeto' => TipoProjetoAtividade::Proj,
                'postes_projetados' => 250,
            ],
        ]);

        $this->assertEqualsWithDelta(740.0, $bonusMisto, 0.0001);
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

        $this->assertEqualsWithDelta(700.0, $bonusPorColaborador[1], 0.0001);
        $this->assertSame(0.0, $bonusPorColaborador[2]);
    }

    public function test_formatar_meta_exibe_postes_por_categoria(): void
    {
        $this->assertSame('500 / 400', $this->calculator->formatarMetaDesenhoCad(500));
        $this->assertSame('500 / 300', $this->calculator->formatarMetaProjetoCad(500));
        $this->assertSame('300 / 230', $this->calculator->formatarMetaProj(300));
        $this->assertSame(
            '500 / 400 - 500 / 300 - 300 / 230',
            $this->calculator->formatarMeta(
                postesDesenhoCad: 500,
                postesProjetoCad: 500,
                postesProjetoProj: 300,
            ),
        );
    }

    public function test_aplica_teto_de_setenta_por_cento_da_remuneracao(): void
    {
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
        $colaborador->total_postes_desenho_cad = 0;
        $colaborador->total_postes_projeto_cad = 500;
        $colaborador->total_postes_projetados_proj = 0;

        $this->calculator->enriquecerColaborador($colaborador);

        $this->assertSame('0 / 400', $colaborador->meta_desenho_cad);
        $this->assertSame('500 / 300', $colaborador->meta_projeto_cad);
        $this->assertSame('0 / 230', $colaborador->meta_proj);
        $this->assertSame(500, $colaborador->total_postes);
        $this->assertEqualsWithDelta(700.0, $colaborador->total_bonus, 0.0001);
    }
}
