<?php

namespace Tests\Unit\Exports;

use App\Exports\ExportacaoProdutividadeExport;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExportacaoProdutividadeExportTest extends TestCase
{
    public function test_inclui_linha_em_branco_entre_detalhe_e_dados_gerais(): void
    {
        $export = $this->makeExport([
            ['Projeto A', 'Atividade 1', '10/06/2026', 'CAD', 200, 400, '1h 0min'],
            ['Projeto B', 'Atividade 2', '11/06/2026', 'PROJ', 50, 5, '2h 0min'],
        ]);

        $linhas = $export->array();

        $this->assertSame(['Projeto', 'Atividade', 'Data', 'Tipo de Projeto', 'Postes Desenhados', 'Postes Projetados', 'Horas'], $linhas[0]);
        $this->assertSame(['Projeto B', 'Atividade 2', '11/06/2026', 'PROJ', 50, 5, '2h 0min'], $linhas[2]);
        $this->assertSame(['', '', '', '', '', '', ''], $linhas[3]);
        $this->assertSame(['Competência', 'Projetos', 'Postes CAD', 'Postes PROJ', 'Postes Total', 'Bônus'], $linhas[4]);
        $this->assertSame(['Junho - 2026', 2, 600, 5, 605, 0.0], $linhas[5]);
    }

    public function test_mescla_colunas_a_f_na_linha_da_legenda(): void
    {
        $export = $this->makeExport([
            ['Projeto A', 'Atividade 1', '10/06/2026', 'CAD', 200, 400, '1h 0min'],
        ]);

        $conteudo = Excel::raw($export, ExcelFormat::XLSX);
        $caminho = tempnam(sys_get_temp_dir(), 'exportacao-').'.xlsx';
        file_put_contents($caminho, $conteudo);

        try {
            $spreadsheet = IOFactory::load($caminho);
            $merged = $spreadsheet->getActiveSheet()->getMergeCells();

            // Cabeçalho + 1 detalhe + branco + cabeçalho resumo + resumo + legenda = linha 6
            $this->assertArrayHasKey('A6:G6', $merged);
        } finally {
            @unlink($caminho);
        }
    }

    /**
     * @param  list<array{0: string, 1: string, 2: string, 3: string, 4: int, 5: int, 6: string}>  $linhasDetalhe
     */
    private function makeExport(array $linhasDetalhe): ExportacaoProdutividadeExport
    {
        $postesCad = 0;
        $postesProj = 0;

        foreach ($linhasDetalhe as $linha) {
            if ($linha[3] === 'PROJ') {
                $postesProj += $linha[5];
            } else {
                $postesCad += $linha[4] + $linha[5];
            }
        }

        return new ExportacaoProdutividadeExport(
            linhasDetalhe: $linhasDetalhe,
            resumo: [
                'competencia' => 'Junho - 2026',
                'projetos' => count($linhasDetalhe),
                'postes_cad' => $postesCad,
                'postes_proj' => $postesProj,
                'postes_total' => $postesCad + $postesProj,
                'bonus' => 0.0,
            ],
        );
    }
}
