<?php

namespace App\Models;

use App\Enums\TipoProjetoAtividade;
use Carbon\Carbon;
use Database\Factories\ProjetoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Projeto extends Model
{
    /** @use HasFactory<ProjetoFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'nome',
        'colaborador_responsavel_id',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Projeto $projeto): void {
            $projeto->atividades()->delete();
        });
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_responsavel_id')->withTrashed();
    }

    public function atividades(): HasMany
    {
        return $this->hasMany(Atividade::class);
    }

    public function logAtividades(): HasMany
    {
        return $this->hasMany(LogAtividade::class);
    }

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
    public function totaisGlobaisDashboard(?string $mesAno = null): object
    {
        $atividades = Atividade::query()
            ->join('projetos', 'projetos.id', '=', 'atividades.projeto_id')
            ->whereNull('projetos.deleted_at')
            ->where('atividades.tipo_projeto', TipoProjetoAtividade::Cad->value)
            ->whereNotNull('atividades.data_conclusao')
            ->tap(fn (Builder $query) => $this->aplicarCompetenciaDashboard($query, $mesAno))
            ->selectRaw('COALESCE(SUM(atividades.extensao_projeto), 0) as total_extensao_projeto')
            ->selectRaw('COALESCE(SUM(atividades.postes_projetados), 0) as total_postes_projetados')
            ->selectRaw('COALESCE(SUM(COALESCE(atividades.duracao_minutos, 0) * 60), 0) as total_segundos')
            ->first();

        $totalProjetos = (int) Projeto::query()
            ->whereHas('atividades', function (Builder $query) use ($mesAno): void {
                $query
                    ->where('tipo_projeto', TipoProjetoAtividade::Cad->value)
                    ->whereNotNull('data_conclusao')
                    ->when($mesAno, function (Builder $query) use ($mesAno): void {
                        $inicio = Carbon::createFromFormat('Y-m', $mesAno)->startOfMonth();

                        $query
                            ->where('data_conclusao', '>=', $inicio->toDateString())
                            ->where('data_conclusao', '<', $inicio->copy()->addMonth()->startOfMonth()->toDateString());
                    });
            })
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
    public function estatisticasDashboard(?string $mesAno = null): Builder
    {
        return Projeto::query()
            ->leftJoin('atividades', function ($join): void {
                $join->on('atividades.projeto_id', '=', 'projetos.id')
                    ->whereNull('atividades.deleted_at')
                    ->whereNotNull('atividades.data_conclusao');
            })
            ->leftJoin('colaboradores', 'colaboradores.id', '=', 'projetos.colaborador_responsavel_id')
            ->whereNotNull('atividades.data_conclusao')
            ->tap(fn (Builder $query) => $this->aplicarCompetenciaDashboard($query, $mesAno))
            ->groupBy('projetos.id', 'projetos.nome', 'projetos.created_at', 'colaboradores.nome')
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
            ->selectRaw('COALESCE(SUM(COALESCE(atividades.duracao_minutos, 0) * 60), 0) as total_segundos');
    }

    private function aplicarCompetenciaDashboard(Builder $query, ?string $mesAno): void
    {
        $query->when($mesAno, function (Builder $query) use ($mesAno): void {
            $inicio = Carbon::createFromFormat('Y-m', $mesAno)->startOfMonth();

            $query
                ->where('atividades.data_conclusao', '>=', $inicio->toDateString())
                ->where('atividades.data_conclusao', '<', $inicio->copy()->addMonth()->startOfMonth()->toDateString());
        });
    }
}
