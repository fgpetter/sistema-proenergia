<?php

namespace App\Livewire\Admin;

use App\Models\Colaborador;
use App\Models\Parte;
use App\Models\Projeto;
use App\Queries\RelatorioColaboradoresProdutividade;
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

        return app(RelatorioColaboradoresProdutividade::class)
            ->agregar(
                colaboradorId: $this->colaboradorIdEscopo(),
                projetoId: $this->projetoId,
                mesAno: $this->mesAno,
                coordenadorId: $this->coordenadorId,
            )
            ->map(function (Colaborador $colaborador) use ($bonusPorColaborador): Colaborador {
                $calculator = app(BonusColaboradorCalculator::class);

                $bonusBruto = (float) ($bonusPorColaborador[$colaborador->id] ?? 0);
                $colaborador->total_bonus = $calculator->aplicarTeto(
                    $bonusBruto,
                    $colaborador->remuneracao,
                );
                $colaborador->meta_cad = $calculator->formatarMetaCad(
                    $colaborador->total_postes_projetados_cad,
                );
                $colaborador->meta_proj = $calculator->formatarMetaProj(
                    $colaborador->total_postes_projetados_proj,
                );
                $colaborador->total_postes = (int) $colaborador->total_postes_projetados_cad
                    + (int) $colaborador->total_postes_projetados_proj;

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
