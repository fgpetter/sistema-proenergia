<?php

namespace App\Queries;

use App\Enums\TipoProjetoAtividade;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Support\BonusColaboradorCalculator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardMetrics
{
    /**
     * @return object{
     *     totalProjetos: int,
     *     totalExtensaoProjeto: int,
     *     totalPostesProjetados: int,
     *     totalSegundos: int,
     *     mediaExtensaoPorProjeto: float,
     *     mediaPostesPorProjeto: float,
     *     mediaSegundosPorProjeto: float,
     *     vaoMedioProjetado: float,
     * }
     */
    public function totaisGlobais(?string $mesAno = null): object
    {
        $atividades = Atividade::query()
            ->join('projetos', 'projetos.id', '=', 'atividades.projeto_id')
            ->where('atividades.tipo_projeto', TipoProjetoAtividade::Cad->value)
            ->tap(fn (Builder $query) => $this->aplicarCompetencia($query, $mesAno))
            ->selectRaw('COALESCE(SUM(atividades.extensao_projeto), 0) as total_extensao_projeto')
            ->selectRaw('COALESCE(SUM(atividades.postes_projetados), 0) as total_postes_projetados')
            ->selectRaw('COALESCE(SUM('.$this->sqlSegundosAtividade().'), 0) as total_segundos')
            ->first();

        $totalProjetos = (int) Projeto::query()
            ->tap(fn (Builder $query) => $this->aplicarCompetencia($query, $mesAno))
            ->whereHas('atividades', fn (Builder $query) => $query->where('tipo_projeto', TipoProjetoAtividade::Cad->value))
            ->count();

        $totalExtensaoProjeto = (int) $atividades->total_extensao_projeto;
        $totalPostesProjetados = (int) $atividades->total_postes_projetados;
        $totalSegundos = (int) $atividades->total_segundos;

        return (object) [
            'totalProjetos' => $totalProjetos,
            'totalExtensaoProjeto' => $totalExtensaoProjeto,
            'totalPostesProjetados' => $totalPostesProjetados,
            'totalSegundos' => $totalSegundos,
            'mediaExtensaoPorProjeto' => $totalProjetos > 0 ? (float) ($totalExtensaoProjeto / $totalProjetos) : 0.0,
            'mediaPostesPorProjeto' => $totalProjetos > 0 ? (float) ($totalPostesProjetados / $totalProjetos) : 0.0,
            'mediaSegundosPorProjeto' => $totalProjetos > 0 ? (float) ($totalSegundos / $totalProjetos) : 0.0,
            'vaoMedioProjetado' => $totalPostesProjetados > 0 ? (float) ($totalExtensaoProjeto / $totalPostesProjetados) : 0.0,
        ];
    }

    /**
     * @return Builder<Projeto>
     */
    public function estatisticasPorProjeto(?string $mesAno = null): Builder
    {
        return Projeto::query()
            ->leftJoin('atividades', 'atividades.projeto_id', '=', 'projetos.id')
            ->leftJoin('colaboradores', 'colaboradores.id', '=', 'projetos.colaborador_responsavel_id')
            ->tap(fn (Builder $query) => $this->aplicarCompetencia($query, $mesAno))
            ->groupBy(
                'projetos.id',
                'projetos.nome',
                'projetos.created_at',
                'colaboradores.nome',
            )
            ->orderByDesc('projetos.created_at')
            ->select([
                'projetos.id',
                'projetos.nome',
                'projetos.created_at',
                'colaboradores.nome as coordenador',
            ])
            ->selectRaw('COALESCE(SUM(atividades.extensao_desenho), 0) as total_extensao_desenho')
            ->selectRaw('COALESCE(SUM(atividades.extensao_projeto), 0) as total_extensao_projeto')
            ->selectRaw('COALESCE(SUM(atividades.postes_desenhados), 0) as total_postes_desenhados')
            ->selectRaw('COALESCE(SUM(atividades.postes_projetados), 0) as total_postes_projetados')
            ->selectRaw('COALESCE(SUM('.$this->sqlSegundosAtividade().'), 0) as total_segundos');
    }

    /**
     * Ranking de Projeto CAD por colaborador na Competência.
     *
     * @return Collection<int, object{nome: string, total: int, acimaDaMeta: bool|null}>
     */
    public function producaoPorColaborador(?string $mesAno = null): Collection
    {
        $aplicaCorMeta = $mesAno !== null;
        $limiteMeta = BonusColaboradorCalculator::LIMITE_POSTES_PROJETO_CAD;

        return Colaborador::query()
            ->join('atividades', 'atividades.colaborador_id', '=', 'colaboradores.id')
            ->join('projetos', 'projetos.id', '=', 'atividades.projeto_id')
            ->where('atividades.tipo_projeto', TipoProjetoAtividade::Cad->value)
            ->tap(fn (Builder $query) => $this->aplicarCompetencia($query, $mesAno))
            ->groupBy('colaboradores.id', 'colaboradores.nome')
            ->havingRaw('COALESCE(SUM(atividades.postes_projetados), 0) > 0')
            ->orderByDesc('total')
            ->orderBy('colaboradores.nome')
            ->select([
                'colaboradores.nome',
            ])
            ->selectRaw('COALESCE(SUM(atividades.postes_projetados), 0) as total')
            ->get()
            ->map(function (Colaborador $colaborador) use ($aplicaCorMeta, $limiteMeta): object {
                $total = (int) $colaborador->total;

                return (object) [
                    'nome' => $colaborador->nome,
                    'total' => $total,
                    'acimaDaMeta' => $aplicaCorMeta ? $total >= $limiteMeta : null,
                ];
            })
            ->values();
    }

    /**
     * Projeto CAD do mês calendário atual, acumulado por semana de atividades.created_at.
     * Semana N = soma do dia 1 até o fim do bloco N (1–7, 1–14, 1–21, …).
     *
     * @return Collection<int, object{semana: int, rotulo: string, total: int}>
     */
    public function evolucaoSemanalPostes(): Collection
    {
        $inicio = now()->copy()->startOfMonth();
        $fim = now()->copy()->endOfMonth();
        $diasNoMes = (int) $inicio->daysInMonth;
        $totalSemanas = (int) intdiv($diasNoMes - 1, 7) + 1;

        $totaisPorBloco = array_fill(1, $totalSemanas, 0);

        $linhas = Atividade::query()
            ->where('atividades.tipo_projeto', TipoProjetoAtividade::Cad->value)
            ->whereBetween('atividades.created_at', [$inicio, $fim])
            ->toBase()
            ->get(['atividades.created_at', 'atividades.postes_projetados']);

        foreach ($linhas as $linha) {
            $dia = Carbon::parse($linha->created_at)->day;
            $bloco = (int) intdiv($dia - 1, 7) + 1;
            $totaisPorBloco[$bloco] += (int) $linha->postes_projetados;
        }

        $acumulado = 0;

        return collect(range(1, $totalSemanas))
            ->map(function (int $semana) use (&$acumulado, $totaisPorBloco): object {
                $acumulado += $totaisPorBloco[$semana];

                return (object) [
                    'semana' => $semana,
                    'rotulo' => (string) $semana,
                    'total' => $acumulado,
                ];
            });
    }

    private function sqlSegundosAtividade(): string
    {
        return 'COALESCE(atividades.duracao_minutos, 0) * 60';
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
