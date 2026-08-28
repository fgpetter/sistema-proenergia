<?php

namespace Tests\Unit\Exports;

use App\Exports\PlanilhaContabilidadeExport;
use Tests\TestCase;

class PlanilhaContabilidadeExportTest extends TestCase
{
    public function test_monta_cabecalho_e_linhas_com_nome_e_premio(): void
    {
        $export = new PlanilhaContabilidadeExport([
            ['nome' => 'João Silva', 'premio' => 700.0],
        ]);

        $linhas = $export->array();

        $this->assertSame(
            ['', 'Nome', 'VA', 'VT', 'Co-Participação Plano Saúde', 'Bonificações', 'Ajuda Custo Home', 'Prêmio Produtivid.', 'Horas Extras 50%', 'Horas Extras 70%', 'Horas Extras 130%', 'Faltas', 'Obs:'],
            $linhas[0],
        );
        $this->assertSame(1, $linhas[1][0]);
        $this->assertSame('João Silva', $linhas[1][1]);
        $this->assertSame(700.0, $linhas[1][7]);
        $this->assertSame('', $linhas[1][2]);
        $this->assertSame('=SUM(H2:H2)', $linhas[2][7]);
    }
}
