<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlanilhaContabilidadeExport implements FromArray, WithEvents
{
    /**
     * @param  list<array{nome: string, premio: float}>  $linhas
     */
    public function __construct(
        private array $linhas,
    ) {}

    public function array(): array
    {
        $cabecalho = [
            '',
            'Nome',
            'VA',
            'VT',
            'Co-Participação Plano Saúde',
            'Bonificações',
            'Ajuda Custo Home',
            'Prêmio Produtivid.',
            'Horas Extras 50%',
            'Horas Extras 70%',
            'Horas Extras 130%',
            'Faltas',
            'Obs:',
        ];

        $dados = array_map(
            fn (array $linha, int $indice): array => [
                $indice + 1,
                $linha['nome'],
                '',
                '',
                '',
                '',
                '',
                $linha['premio'],
                '',
                '',
                '',
                '',
                '',
            ],
            $this->linhas,
            array_keys($this->linhas),
        );

        $linhaTotal = count($this->linhas) + 2;
        $rodape = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '=SUM(H2:H'.($linhaTotal - 1).')',
            '',
            '',
            '',
            '',
            '',
        ];

        return [
            $cabecalho,
            ...$dados,
            $rodape,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                /** @var Worksheet $sheet */
                $sheet = $event->sheet->getDelegate();
                $ultimaLinha = count($this->linhas) + 2;
                $sheet->getStyle("H{$ultimaLinha}")->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            },
        ];
    }
}
