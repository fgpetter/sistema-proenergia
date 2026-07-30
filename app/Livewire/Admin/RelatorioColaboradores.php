<?php

namespace App\Livewire\Admin;

use App\Enums\TipoProjetoParte;
use App\Exports\ExportacaoProdutividadeExport;
use App\Models\Colaborador;
use App\Models\Parte;
use App\Models\Projeto;
use App\Queries\RelatorioColaboradoresProdutividade;
use App\Support\BonusColaboradorCalculator;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RelatorioColaboradores extends Component
{
    #[Url(as: 'projeto')]
    public ?int $projetoId = null;

    #[Url(as: 'mes')]
    public ?string $mesAno = null;

    #[Url(as: 'coord')]
    public ?int $coordenadorId = null;

    #[Computed]
    public function projetos(): Collection
    {
        return Projeto::query()
            ->orderBy('nome')
            ->get(['id', 'nome']);
    }

    #[Computed]
    public function coordenadores(): Collection
    {
        return Colaborador::query()
            ->whereIn('id', Projeto::query()
                ->whereNotNull('colaborador_responsavel_id')
                ->pluck('colaborador_responsavel_id'))
            ->orderBy('nome')
            ->get(['id', 'nome']);
    }

    /**
     * @return Collection<string, string>
     */
    #[Computed]
    public function competenciasDisponiveis(): Collection
    {
        return Projeto::query()
            ->whereNotNull('created_at')
            ->orderByDesc('created_at')
            ->get(['created_at'])
            ->map(fn (Projeto $projeto): string => $projeto->created_at->format('Y-m'))
            ->unique()
            ->values()
            ->mapWithKeys(fn (string $competencia): array => [
                $competencia => $this->formatarCompetencia($competencia),
            ]);
    }

    #[Computed]
    public function produtividadeColaboradores(): Collection
    {
        $calculator = app(BonusColaboradorCalculator::class);

        return app(RelatorioColaboradoresProdutividade::class)
            ->agregar(
                colaboradorId: $this->colaboradorIdEscopo(),
                projetoId: $this->projetoId,
                mesAno: $this->mesAno,
                coordenadorId: $this->coordenadorId,
            )
            ->map(fn (Colaborador $colaborador): Colaborador => $calculator->enriquecerColaborador($colaborador));
    }

    #[Computed]
    public function podeExportarProdutividade(): bool
    {
        $user = auth()->user();

        return $user !== null
            && $user->isPrestador()
            && $user->colaborador !== null;
    }

    public function exportarProdutividade(): BinaryFileResponse
    {
        abort_unless($this->podeExportarProdutividade, 403);

        $this->validate([
            'mesAno' => ['required', 'date_format:Y-m'],
        ], [
            'mesAno.required' => 'Selecione uma competência para baixar a exportação.',
            'mesAno.date_format' => 'A competência selecionada é inválida.',
        ]);

        $calculator = app(BonusColaboradorCalculator::class);

        $partes = app(RelatorioColaboradoresProdutividade::class)->listarPartes(
            colaboradorId: $this->colaboradorIdEscopo(),
            projetoId: $this->projetoId,
            mesAno: $this->mesAno,
            coordenadorId: $this->coordenadorId,
        );

        $postesCad = (int) $partes
            ->filter(fn (Parte $parte): bool => ($parte->tipo_projeto ?? TipoProjetoParte::Cad) !== TipoProjetoParte::Proj)
            ->sum('postes_projetados');
        $postesProj = (int) $partes
            ->filter(fn (Parte $parte): bool => $parte->tipo_projeto === TipoProjetoParte::Proj)
            ->sum('postes_projetados');
        $totalProjetos = $partes->pluck('projeto_id')->unique()->count();

        $bonusBruto = $calculator->calcularDePartes($partes);
        $bonus = $calculator->aplicarTeto(
            $bonusBruto,
            auth()->user()->colaborador?->remuneracao,
        );

        $linhasDetalhe = $partes->map(function (Parte $parte): array {
            return [
                $parte->projeto?->nome ?? '',
                $parte->nome,
                $parte->projeto?->created_at?->format('d/m/Y') ?? '',
                $parte->tipo_projeto?->value ?? TipoProjetoParte::Cad->value,
                (int) $parte->postes_projetados,
                $this->formatarHoras($parte),
            ];
        })->all();

        return Excel::download(
            new ExportacaoProdutividadeExport(
                linhasDetalhe: $linhasDetalhe,
                resumo: [
                    'competencia' => $this->formatarCompetencia($this->mesAno),
                    'projetos' => $totalProjetos,
                    'postes_cad' => $postesCad,
                    'postes_proj' => $postesProj,
                    'postes_total' => $postesCad + $postesProj,
                    'bonus' => $bonus,
                ],
            ),
            'exportacao-produtividade-'.$this->mesAno.'.xlsx',
        );
    }

    protected function colaboradorIdEscopo(): ?int
    {
        $user = auth()->user();

        if ($user === null || $user->isAdminOrSuperAdmin() || $user->isCoordenador()) {
            return null;
        }

        return $user->colaborador?->id;
    }

    protected function formatarCompetencia(string $competencia): string
    {
        $data = Carbon::createFromFormat('Y-m', $competencia)->locale('pt_BR');

        return ucfirst($data->translatedFormat('F')).' - '.$data->format('Y');
    }

    protected function formatarHoras(Parte $parte): string
    {
        $segundos = 0;

        if ($parte->data_hora_inicio !== null && $parte->data_hora_fim !== null) {
            $segundos = (int) $parte->data_hora_inicio->diffInSeconds($parte->data_hora_fim);
        }

        $interval = CarbonInterval::seconds($segundos)->cascade();

        return ((int) $interval->totalHours).'h '.$interval->minutes.'min';
    }

    public function render(): View
    {
        return view('livewire.admin.relatorio-colaboradores');
    }
}
