<?php

namespace App\Livewire\Painel;

use App\Models\Colaborador;
use App\Queries\RelatorioColaboradoresProdutividade;
use App\Support\BonusColaboradorCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class PerformanceColaboradores extends Component
{
    #[Reactive]
    public ?string $mesAno = null;

    #[Computed]
    public function produtividadeColaboradores(): Collection
    {
        $calculator = app(BonusColaboradorCalculator::class);

        return app(RelatorioColaboradoresProdutividade::class)
            ->agregar(mesAno: $this->mesAno)
            ->map(fn (Colaborador $colaborador): Colaborador => $calculator->enriquecerColaborador($colaborador));
    }

    public function render(): View
    {
        return view('livewire.painel.performance-colaboradores');
    }
}
