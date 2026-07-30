<?php

namespace App\Queries;

use App\Models\Colaborador;
use App\Models\Parte;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RelatorioColaboradoresProdutividade
{
    /**
     * @return Collection<int, Colaborador>
     */
    public function agregar(
        ?int $colaboradorId = null,
        ?int $projetoId = null,
        ?string $mesAno = null,
        ?int $coordenadorId = null,
    ): Collection {
        return Colaborador::query()
            ->join('partes', 'partes.colaborador_id', '=', 'colaboradores.id')
            ->join('projetos', 'projetos.id', '=', 'partes.projeto_id')
            ->tap(fn (Builder $query) => $this->aplicarFiltros(
                $query,
                colaboradorId: $colaboradorId,
                projetoId: $projetoId,
                mesAno: $mesAno,
                coordenadorId: $coordenadorId,
                colaboradorColumn: 'colaboradores.id',
            ))
            ->groupBy('colaboradores.id', 'colaboradores.nome', 'colaboradores.remuneracao')
            ->orderBy('colaboradores.nome')
            ->select([
                'colaboradores.id',
                'colaboradores.nome',
                'colaboradores.remuneracao',
            ])
            ->selectRaw('COUNT(DISTINCT partes.projeto_id) as total_projetos')
            ->selectRaw('COALESCE(SUM(partes.extensao_desenho), 0) as total_extensao_desenho')
            ->selectRaw('COALESCE(SUM(partes.extensao_projeto), 0) as total_extensao_projeto')
            ->selectRaw('COALESCE(SUM(partes.postes_desenhados), 0) as total_postes_desenhados')
            ->selectRaw('COALESCE(SUM(partes.postes_projetados), 0) as total_postes_projetados')
            ->selectRaw("COALESCE(SUM(CASE WHEN partes.tipo_projeto = 'PROJ' THEN partes.postes_projetados ELSE 0 END), 0) as total_postes_projetados_proj")
            ->selectRaw("COALESCE(SUM(CASE WHEN partes.tipo_projeto = 'PROJ' THEN 0 ELSE partes.postes_projetados END), 0) as total_postes_projetados_cad")
            ->selectRaw('COALESCE(SUM(CASE WHEN partes.data_hora_inicio IS NOT NULL AND partes.data_hora_fim IS NOT NULL THEN TIMESTAMPDIFF(SECOND, partes.data_hora_inicio, partes.data_hora_fim) ELSE 0 END), 0) as total_segundos')
            ->get();
    }

    /**
     * @return Collection<int, Parte>
     */
    public function listarPartes(
        ?int $colaboradorId = null,
        ?int $projetoId = null,
        ?string $mesAno = null,
        ?int $coordenadorId = null,
    ): Collection {
        return Parte::query()
            ->select('partes.*')
            ->join('projetos', 'projetos.id', '=', 'partes.projeto_id')
            ->with('projeto')
            ->tap(fn (Builder $query) => $this->aplicarFiltros(
                $query,
                colaboradorId: $colaboradorId,
                projetoId: $projetoId,
                mesAno: $mesAno,
                coordenadorId: $coordenadorId,
                colaboradorColumn: 'partes.colaborador_id',
            ))
            ->orderBy('projetos.created_at')
            ->orderBy('partes.nome')
            ->get();
    }

    private function aplicarFiltros(
        Builder $query,
        ?int $colaboradorId,
        ?int $projetoId,
        ?string $mesAno,
        ?int $coordenadorId,
        string $colaboradorColumn,
    ): void {
        $query
            ->when($colaboradorId !== null, function (Builder $query) use ($colaboradorId, $colaboradorColumn): void {
                $query->where($colaboradorColumn, $colaboradorId);
            })
            ->when($projetoId, function (Builder $query) use ($projetoId): void {
                $query->where('partes.projeto_id', $projetoId);
            })
            ->when($mesAno, function (Builder $query) use ($mesAno): void {
                $inicio = Carbon::createFromFormat('Y-m', $mesAno)->startOfMonth();
                $fim = $inicio->copy()->endOfMonth();

                $query->whereBetween('projetos.created_at', [$inicio, $fim]);
            })
            ->when($coordenadorId, function (Builder $query) use ($coordenadorId): void {
                $query->where('projetos.colaborador_responsavel_id', $coordenadorId);
            });
    }
}
