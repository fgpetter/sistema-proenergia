<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportacaoProdutividadeExport implements FromArray, WithEvents
{
    /**
     * @param  list<array{0: string, 1: string, 2: string, 3: string, 4: int, 5: int, 6: string}>  $linhasDetalhe
     * @param  array{
     *     competencia: string,
     *     projetos: int,
     *     postes_desenho_cad: int,
     *     postes_projeto_cad: int,
     *     postes_proj: int,
     *     postes_total: int,
     *     bonus: float
     * }  $resumo
     */
    public function __construct(
        private array $linhasDetalhe,
        private array $resumo,
    ) {}

    public function array(): array
    {
        return [
            ['Projeto', 'Atividade', 'Data', 'Tipo de Projeto', 'Postes Desenhados', 'Postes Projetados', 'Horas'],
            ...$this->linhasDetalhe,
            ['', '', '', '', '', '', ''],
            ['Competência', 'Projetos', 'Desenho CAD', 'Projeto CAD', 'Projeto PROJ', 'Postes Total', 'Bônus'],
            [
                $this->resumo['competencia'],
                $this->resumo['projetos'],
                $this->resumo['postes_desenho_cad'],
                $this->resumo['postes_projeto_cad'],
                $this->resumo['postes_proj'],
                $this->resumo['postes_total'],
                $this->resumo['bonus'],
            ],
            ['* a meta para Desenho CAD é de 400 postes, para Projeto CAD é de 300 postes, para Projeto PROJ é de 230 postes.'],
        ];
    }

    public function registerEvents(): array
    {
        $linhaLegenda = count($this->linhasDetalhe) + 5;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($linhaLegenda): void {
                /** @var Worksheet $sheet */
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells("A{$linhaLegenda}:G{$linhaLegenda}");
            },
        ];
    }
}
