<?php

namespace App\Livewire\Painel;

use App\Models\Colaborador;
use App\Models\Projeto;
use App\Queries\RelatorioColaboradoresProdutividade;
use App\Support\BonusColaboradorCalculator;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class PerformanceColaboradores extends Component
{
    #[Url(as: 'mes')]
    public ?string $mesAno = null;

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
            ->agregar(mesAno: $this->mesAno)
            ->map(fn (Colaborador $colaborador): Colaborador => $calculator->enriquecerColaborador($colaborador));
    }

    protected function formatarCompetencia(string $competencia): string
    {
        $data = Carbon::createFromFormat('Y-m', $competencia)->locale('pt_BR');

        return ucfirst($data->translatedFormat('F')).' - '.$data->format('Y');
    }

    public function render(): View
    {
        return view('livewire.painel.performance-colaboradores');
    }
}
