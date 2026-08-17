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
     *     postes_cad: int,
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
            ['Competência', 'Projetos', 'Postes CAD', 'Postes PROJ', 'Postes Total', 'Bônus'],
            [
                $this->resumo['competencia'],
                $this->resumo['projetos'],
                $this->resumo['postes_cad'],
                $this->resumo['postes_proj'],
                $this->resumo['postes_total'],
                $this->resumo['bonus'],
            ],
            ['* a meta para projetos no PROJ é de 230 postes, para projetos no CAD é de 300 postes.'],
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
