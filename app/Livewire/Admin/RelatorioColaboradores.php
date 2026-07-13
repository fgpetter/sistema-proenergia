<?php

namespace App\Livewire\Admin;

use App\Models\Colaborador;
use App\Models\Parte;
use App\Models\Projeto;
use App\Support\BonusColaboradorCalculator;
use Carbon\Carbon;
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
        $bonusPorColaborador = $this->bonusPorColaborador();

        $colaboradorIdEscopo = $this->colaboradorIdEscopo();

        return Colaborador::query()
            ->join('partes', 'partes.colaborador_id', '=', 'colaboradores.id')
            ->join('projetos', 'projetos.id', '=', 'partes.projeto_id')
            ->when($colaboradorIdEscopo !== null, function (Builder $query) use ($colaboradorIdEscopo): void {
                $query->where('colaboradores.id', $colaboradorIdEscopo);
            })
            ->when($this->projetoId, function (Builder $query): void {
                $query->where('partes.projeto_id', $this->projetoId);
            })
            ->when($this->mesAno, function (Builder $query): void {
                $this->aplicarFiltroCompetencia($query);
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
            ->get()
            ->map(function (Colaborador $colaborador) use ($bonusPorColaborador): Colaborador {
                $colaborador->total_bonus = (float) ($bonusPorColaborador[$colaborador->id] ?? 0);

                return $colaborador;
            });
    }

    /**
     * @return Collection<int|string, float>
     */
    protected function bonusPorColaborador(): Collection
    {
        $colaboradorIdEscopo = $this->colaboradorIdEscopo();

        $partes = Parte::query()
            ->join('projetos', 'projetos.id', '=', 'partes.projeto_id')
            ->whereNotNull('partes.colaborador_id')
            ->when($colaboradorIdEscopo !== null, function (Builder $query) use ($colaboradorIdEscopo): void {
                $query->where('partes.colaborador_id', $colaboradorIdEscopo);
            })
            ->when($this->projetoId, function (Builder $query): void {
                $query->where('partes.projeto_id', $this->projetoId);
            })
            ->when($this->mesAno, function (Builder $query): void {
                $this->aplicarFiltroCompetencia($query);
            })
            ->when($this->coordenadorId, function (Builder $query): void {
                $query->where('projetos.colaborador_responsavel_id', $this->coordenadorId);
            })
            ->get([
                'partes.colaborador_id',
                'partes.projeto_id',
                'partes.postes_desenhados',
                'partes.postes_projetados',
                'partes.tipo_projeto',
            ]);

        return app(BonusColaboradorCalculator::class)
            ->somarPorColaborador($partes);
    }

    protected function colaboradorIdEscopo(): ?int
    {
        $user = auth()->user();

        if ($user === null || $user->isAdminOrSuperAdmin() || $user->isCoordenador()) {
            return null;
        }

        return $user->colaborador?->id;
    }

    protected function aplicarFiltroCompetencia(Builder $query): void
    {
        $inicio = Carbon::createFromFormat('Y-m', $this->mesAno)->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();

        $query->whereBetween('projetos.created_at', [$inicio, $fim]);
    }

    protected function formatarCompetencia(string $competencia): string
    {
        $data = Carbon::createFromFormat('Y-m', $competencia)->locale('pt_BR');

        return ucfirst($data->translatedFormat('F')).' - '.$data->format('Y');
    }

    public function render(): View
    {
        return view('livewire.admin.relatorio-colaboradores');
    }
}
