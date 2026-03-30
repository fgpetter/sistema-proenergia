<?php

namespace App\Livewire\Admin;

use App\Models\Colaborador;
use App\Models\Projeto;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class RelatorioColaboradores extends Component
{
    #[Url(as: 'projeto')]
    public ?int $projetoId = null;

    #[Url(as: 'inicio')]
    public ?string $dataInicio = null;

    #[Url(as: 'fim')]
    public ?string $dataFim = null;

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

    #[Computed]
    public function produtividadeColaboradores(): Collection
    {
        return Colaborador::query()
            ->join('partes', 'partes.colaborador_id', '=', 'colaboradores.id')
            ->join('projetos', 'projetos.id', '=', 'partes.projeto_id')
            ->when($this->projetoId, function (Builder $query): void {
                $query->where('partes.projeto_id', $this->projetoId);
            })
            ->when($this->dataInicio, function (Builder $query): void {
                $query->whereDate('partes.data_hora_inicio', '>=', $this->dataInicio);
            })
            ->when($this->dataFim, function (Builder $query): void {
                $query->whereDate('partes.data_hora_inicio', '<=', $this->dataFim);
            })
            ->when($this->coordenadorId, function (Builder $query): void {
                $query->where('projetos.colaborador_responsavel_id', $this->coordenadorId);
            })
            ->groupBy('colaboradores.id', 'colaboradores.nome')
            ->orderBy('colaboradores.nome')
            ->select([
                'colaboradores.id',
                'colaboradores.nome',
            ])
            ->selectRaw('COUNT(DISTINCT partes.projeto_id) as total_projetos')
            ->selectRaw('COALESCE(SUM(partes.extensao_desenho), 0) as total_extensao_desenho')
            ->selectRaw('COALESCE(SUM(partes.extensao_projeto), 0) as total_extensao_projeto')
            ->selectRaw('COALESCE(SUM(partes.postes_desenhados), 0) as total_postes_desenhados')
            ->selectRaw('COALESCE(SUM(partes.postes_projetados), 0) as total_postes_projetados')
            ->selectRaw('COALESCE(SUM(CASE WHEN partes.data_hora_inicio IS NOT NULL AND partes.data_hora_fim IS NOT NULL THEN TIMESTAMPDIFF(SECOND, partes.data_hora_inicio, partes.data_hora_fim) ELSE 0 END), 0) as total_segundos')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.admin.relatorio-colaboradores');
    }
}
