<?php

namespace App\Queries;

use App\Models\Colaborador;
use App\Models\Parte;
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
        $partes = Parte::query()
            ->selectRaw('COALESCE(SUM(extensao_desenho), 0) as total_extensao_desenho')
            ->selectRaw('COALESCE(SUM(extensao_projeto), 0) as total_extensao_projeto')
            ->selectRaw('COALESCE(SUM(postes_desenhados), 0) as total_postes_desenhados')
            ->selectRaw('COALESCE(SUM(postes_projetados), 0) as total_postes_projetados')
            ->selectRaw('COALESCE(SUM(CASE WHEN data_hora_inicio IS NOT NULL AND data_hora_fim IS NOT NULL THEN TIMESTAMPDIFF(SECOND, data_hora_inicio, data_hora_fim) ELSE 0 END), 0) as total_segundos')
            ->first();

        return (object) [
            'totalProjetos' => Projeto::query()->count(),
            'totalExtensaoDesenho' => (int) $partes->total_extensao_desenho,
            'totalExtensaoProjeto' => (int) $partes->total_extensao_projeto,
            'totalPostesDesenhados' => (int) $partes->total_postes_desenhados,
            'totalPostesProjetados' => (int) $partes->total_postes_projetados,
            'totalSegundos' => (int) $partes->total_segundos,
        ];
    }

    public function estatisticasPorProjeto(): Collection
    {
        return Projeto::query()
            ->leftJoin('partes', 'partes.projeto_id', '=', 'projetos.id')
            ->groupBy('projetos.id', 'projetos.nome', 'projetos.created_at')
            ->orderByDesc('projetos.created_at')
            ->select([
                'projetos.id',
                'projetos.nome',
                'projetos.created_at',
            ])
            ->selectRaw('COALESCE(SUM(partes.extensao_desenho), 0) as total_extensao_desenho')
            ->selectRaw('COALESCE(SUM(partes.extensao_projeto), 0) as total_extensao_projeto')
            ->selectRaw('COALESCE(SUM(partes.postes_desenhados), 0) as total_postes_desenhados')
            ->selectRaw('COALESCE(SUM(partes.postes_projetados), 0) as total_postes_projetados')
            ->selectRaw('COALESCE(SUM(CASE WHEN partes.data_hora_inicio IS NOT NULL AND partes.data_hora_fim IS NOT NULL THEN TIMESTAMPDIFF(SECOND, partes.data_hora_inicio, partes.data_hora_fim) ELSE 0 END), 0) as total_segundos')
            ->get();
    }

    public function produtividadeColaboradores(): Collection
    {
        return Colaborador::query()
            ->join('partes', 'partes.colaborador_id', '=', 'colaboradores.id')
            ->join('projetos', 'projetos.id', '=', 'partes.projeto_id')
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
}
