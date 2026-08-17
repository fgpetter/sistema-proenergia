<?php

namespace App\Queries;

use App\Models\Atividade;
use App\Models\Projeto;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DashboardMetrics
{
    /**
     * @return object{
     *     totalProjetos: int,
     *     totalExtensaoDesenho: int,
     *     totalExtensaoProjeto: int,
     *     totalPostesDesenhados: int,
     *     totalPostesProjetados: int,
     *     totalSegundos: int,
     * }
     */
    public function totaisGlobais(?string $mesAno = null): object
    {
        $atividades = Atividade::query()
            ->join('projetos', 'projetos.id', '=', 'atividades.projeto_id')
            ->tap(fn (Builder $query) => $this->aplicarCompetencia($query, $mesAno))
            ->selectRaw('COALESCE(SUM(atividades.extensao_desenho), 0) as total_extensao_desenho')
            ->selectRaw('COALESCE(SUM(atividades.extensao_projeto), 0) as total_extensao_projeto')
            ->selectRaw('COALESCE(SUM(atividades.postes_desenhados), 0) as total_postes_desenhados')
            ->selectRaw('COALESCE(SUM(atividades.postes_projetados), 0) as total_postes_projetados')
            ->selectRaw('COALESCE(SUM('.$this->sqlSegundosAtividade().'), 0) as total_segundos')
            ->first();

        return (object) [
            'totalProjetos' => (int) Projeto::query()
                ->tap(fn (Builder $query) => $this->aplicarCompetencia($query, $mesAno))
                ->count(),
            'totalExtensaoDesenho' => (int) $atividades->total_extensao_desenho,
            'totalExtensaoProjeto' => (int) $atividades->total_extensao_projeto,
            'totalPostesDesenhados' => (int) $atividades->total_postes_desenhados,
            'totalPostesProjetados' => (int) $atividades->total_postes_projetados,
            'totalSegundos' => (int) $atividades->total_segundos,
        ];
    }

    /**
     * @return Builder<Projeto>
     */
    public function estatisticasPorProjeto(?string $mesAno = null): Builder
    {
        return Projeto::query()
            ->leftJoin('atividades', 'atividades.projeto_id', '=', 'projetos.id')
            ->tap(fn (Builder $query) => $this->aplicarCompetencia($query, $mesAno))
            ->groupBy('projetos.id', 'projetos.nome', 'projetos.created_at')
            ->orderByDesc('projetos.created_at')
            ->select([
                'projetos.id',
                'projetos.nome',
                'projetos.created_at',
            ])
            ->selectRaw('COALESCE(SUM(atividades.extensao_desenho), 0) as total_extensao_desenho')
            ->selectRaw('COALESCE(SUM(atividades.extensao_projeto), 0) as total_extensao_projeto')
            ->selectRaw('COALESCE(SUM(atividades.postes_desenhados), 0) as total_postes_desenhados')
            ->selectRaw('COALESCE(SUM(atividades.postes_projetados), 0) as total_postes_projetados')
            ->selectRaw('COALESCE(SUM('.$this->sqlSegundosAtividade().'), 0) as total_segundos');
    }

    private function sqlSegundosAtividade(): string
    {
        $inicio = 'atividades.data_hora_inicio';
        $fim = 'atividades.data_hora_fim';

        if (Projeto::query()->getConnection()->getDriverName() === 'sqlite') {
            return "CASE WHEN {$inicio} IS NOT NULL AND {$fim} IS NOT NULL THEN CAST(strftime('%s', {$fim}) AS INTEGER) - CAST(strftime('%s', {$inicio}) AS INTEGER) ELSE 0 END";
        }

        return "CASE WHEN {$inicio} IS NOT NULL AND {$fim} IS NOT NULL THEN TIMESTAMPDIFF(SECOND, {$inicio}, {$fim}) ELSE 0 END";
    }

    private function aplicarCompetencia(Builder $query, ?string $mesAno): void
    {
        $query->when($mesAno, function (Builder $query) use ($mesAno): void {
            $inicio = Carbon::createFromFormat('Y-m', $mesAno)->startOfMonth();
            $fim = $inicio->copy()->endOfMonth();

            $query->whereBetween('projetos.created_at', [$inicio, $fim]);
        });
    }
}
