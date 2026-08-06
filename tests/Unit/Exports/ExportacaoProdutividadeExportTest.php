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
            ['Projeto A', 'Atividade 1', '10/06/2026', 'CAD', 10, '1h 0min'],
            ['Projeto B', 'Atividade 2', '11/06/2026', 'PROJ', 5, '2h 0min'],
        ]);

        $linhas = $export->array();

        $this->assertSame(['Projeto', 'Atividade', 'Data', 'Tipo de Projeto', 'Postes Projetados', 'Horas'], $linhas[0]);
        $this->assertSame(['Projeto B', 'Atividade 2', '11/06/2026', 'PROJ', 5, '2h 0min'], $linhas[2]);
        $this->assertSame(['', '', '', '', '', ''], $linhas[3]);
        $this->assertSame(['Competência', 'Projetos', 'Postes CAD', 'Postes PROJ', 'Postes Total', 'Bônus'], $linhas[4]);
    }

    public function test_mescla_colunas_a_f_na_linha_da_legenda(): void
    {
        $export = $this->makeExport([
            ['Projeto A', 'Atividade 1', '10/06/2026', 'CAD', 10, '1h 0min'],
        ]);

        $conteudo = Excel::raw($export, ExcelFormat::XLSX);
        $caminho = tempnam(sys_get_temp_dir(), 'exportacao-').'.xlsx';
        file_put_contents($caminho, $conteudo);

        try {
            $spreadsheet = IOFactory::load($caminho);
            $merged = $spreadsheet->getActiveSheet()->getMergeCells();

            // Cabeçalho + 1 detalhe + branco + cabeçalho resumo + resumo + legenda = linha 6
            $this->assertArrayHasKey('A6:F6', $merged);
        } finally {
            @unlink($caminho);
        }
    }

    /**
     * @param  list<array{0: string, 1: string, 2: string, 3: string, 4: int, 5: string}>  $linhasDetalhe
     */
    private function makeExport(array $linhasDetalhe): ExportacaoProdutividadeExport
    {
        return new ExportacaoProdutividadeExport(
            linhasDetalhe: $linhasDetalhe,
            resumo: [
                'competencia' => 'Junho - 2026',
                'projetos' => count($linhasDetalhe),
                'postes_cad' => 10,
                'postes_proj' => 5,
                'postes_total' => 15,
                'bonus' => 0.0,
            ],
        );
    }
}
