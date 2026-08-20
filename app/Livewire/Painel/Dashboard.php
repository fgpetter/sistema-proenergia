<?php

namespace App\Livewire\Painel;

use App\Models\Projeto;
use App\Queries\DashboardMetrics;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    #[Url(as: 'mes')]
    public string $mesAno = '';

    public function boot(DashboardMetrics $metrics): void
    {
        $this->metrics = $metrics;
    }

    public function mount(): void
    {
        if ($this->mesAno !== 'todas' && ! preg_match('/^\d{4}-\d{2}$/', $this->mesAno)) {
            $this->mesAno = now()->format('Y-m');
        }
    }

    public function updatedMesAno(): void
    {
        $this->resetPage();
        unset($this->producaoPorColaborador, $this->graficoProducaoPayload);

        $this->dispatch(
            'graficos-dashboard-atualizados',
            producao: $this->graficoProducaoPayload,
            evolucao: $this->graficoEvolucaoPayload,
        );
    }

    #[Computed]
    public function mesAnoFiltro(): ?string
    {
        return $this->mesAno === 'todas' ? null : $this->mesAno;
    }

    /**
     * @return Collection<string, string>
     */
    #[Computed]
    public function competenciasDisponiveis(): Collection
    {
        $atual = now()->format('Y-m');

        $competencias = Projeto::query()
            ->whereNotNull('created_at')
            ->orderByDesc('created_at')
            ->get(['created_at'])
            ->map(fn (Projeto $projeto): string => $projeto->created_at->format('Y-m'))
            ->unique()
            ->values();

        if (! $competencias->contains($atual)) {
            $competencias->prepend($atual);
        }

        return $competencias->mapWithKeys(fn (string $competencia): array => [
            $competencia => $this->formatarCompetencia($competencia),
        ]);
    }

    #[Computed]
    public function totais(): object
    {
        return $this->metrics->totaisGlobais($this->mesAnoFiltro);
    }

    /**
     * @return Collection<int, object{nome: string, total: int, acimaDaMeta: bool|null}>
     */
    #[Computed]
    public function producaoPorColaborador(): Collection
    {
        return $this->metrics->producaoPorColaborador($this->mesAnoFiltro);
    }

    /**
     * @return Collection<int, object{semana: int, rotulo: string, total: int}>
     */
    #[Computed]
    public function evolucaoSemanalPostes(): Collection
    {
        return $this->metrics->evolucaoSemanalPostes();
    }

    #[Computed]
    public function rotuloMesAtual(): string
    {
        return $this->formatarCompetencia(now()->format('Y-m'));
    }

    /**
     * Payload JSON para o gráfico de barras (ranking).
     *
     * @return array{categories: list<string>, totals: list<int>, colors: list<string>, aplicarMeta: bool}
     */
    #[Computed]
    public function graficoProducaoPayload(): array
    {
        $itens = $this->producaoPorColaborador;
        $aplicarMeta = $this->mesAnoFiltro !== null;
        $corNeutra = '#0d9488';
        $corAcima = '#0f766e';
        $corAbaixo = '#ea580c';

        return [
            'categories' => $itens->pluck('nome')->values()->all(),
            'totals' => $itens->pluck('total')->values()->all(),
            'colors' => $itens->map(function (object $item) use ($aplicarMeta, $corNeutra, $corAcima, $corAbaixo): string {
                if (! $aplicarMeta) {
                    return $corNeutra;
                }

                return $item->acimaDaMeta ? $corAcima : $corAbaixo;
            })->values()->all(),
            'aplicarMeta' => $aplicarMeta,
        ];
    }

    /**
     * Payload JSON para o gráfico de área (evolução semanal).
     *
     * @return array{categories: list<string>, totals: list<int>, mes: string}
     */
    #[Computed]
    public function graficoEvolucaoPayload(): array
    {
        $itens = $this->evolucaoSemanalPostes;

        return [
            'categories' => $itens->pluck('rotulo')->values()->all(),
            'totals' => $itens->pluck('total')->values()->all(),
            'mes' => $this->rotuloMesAtual,
        ];
    }

    #[Computed]
    public function estatisticasProjetos(): LengthAwarePaginator
    {
        return $this->metrics->estatisticasPorProjeto($this->mesAnoFiltro)->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.painel.dashboard');
    }

    protected function formatarCompetencia(string $competencia): string
    {
        $data = Carbon::createFromFormat('Y-m', $competencia)->locale('pt_BR');

        return ucfirst($data->translatedFormat('F')).' - '.$data->format('Y');
    }

    private DashboardMetrics $metrics;
}
