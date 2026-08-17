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
