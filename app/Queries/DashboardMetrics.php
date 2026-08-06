<?php

namespace App\Queries;

use App\Models\Atividade;
use App\Models\Projeto;
use Illuminate\Support\Collection;

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
    public function totaisGlobais(): object
    {
        $atividades = Atividade::query()
            ->selectRaw('COALESCE(SUM(extensao_desenho), 0) as total_extensao_desenho')
            ->selectRaw('COALESCE(SUM(extensao_projeto), 0) as total_extensao_projeto')
            ->selectRaw('COALESCE(SUM(postes_desenhados), 0) as total_postes_desenhados')
            ->selectRaw('COALESCE(SUM(postes_projetados), 0) as total_postes_projetados')
            ->selectRaw('COALESCE(SUM(CASE WHEN data_hora_inicio IS NOT NULL AND data_hora_fim IS NOT NULL THEN TIMESTAMPDIFF(SECOND, data_hora_inicio, data_hora_fim) ELSE 0 END), 0) as total_segundos')
            ->first();

        return (object) [
            'totalProjetos' => Projeto::query()->count(),
            'totalExtensaoDesenho' => (int) $atividades->total_extensao_desenho,
            'totalExtensaoProjeto' => (int) $atividades->total_extensao_projeto,
            'totalPostesDesenhados' => (int) $atividades->total_postes_desenhados,
            'totalPostesProjetados' => (int) $atividades->total_postes_projetados,
            'totalSegundos' => (int) $atividades->total_segundos,
        ];
    }

    public function estatisticasPorProjeto(): Collection
    {
        return Projeto::query()
            ->leftJoin('atividades', 'atividades.projeto_id', '=', 'projetos.id')
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
            ->selectRaw('COALESCE(SUM(CASE WHEN atividades.data_hora_inicio IS NOT NULL AND atividades.data_hora_fim IS NOT NULL THEN TIMESTAMPDIFF(SECOND, atividades.data_hora_inicio, atividades.data_hora_fim) ELSE 0 END), 0) as total_segundos')
            ->get();
    }
}
