<?php

namespace App\Models;

use App\Enums\TipoContrato;
use App\Enums\TipoProjetoAtividade;
use App\Support\BonusColaboradorCalculator;
use Carbon\Carbon;
use Database\Factories\ColaboradorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Colaborador extends Model
{
    /** @use HasFactory<ColaboradorFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nome',
        'contrato',
        'remuneracao',
        'user_id',
    ];

    protected $table = 'colaboradores';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contrato' => TipoContrato::class,
            'remuneracao' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return Collection<int, Colaborador>
     */
    public function agregarProdutividade(
        ?int $colaboradorId = null,
        ?int $projetoId = null,
        ?string $mesAno = null,
        ?int $coordenadorId = null,
        bool $limitarAoHoje = false,
    ): Collection {
        return (new Colaborador)->newModelQuery()
            ->join('atividades', 'atividades.colaborador_id', '=', 'colaboradores.id')
            ->join('projetos', 'projetos.id', '=', 'atividades.projeto_id')
            ->whereNull('atividades.deleted_at')
            ->whereNull('projetos.deleted_at')
            ->tap(fn (Builder $query) => (new Atividade)->aplicarFiltrosRelatorio(
                $query,
                $colaboradorId,
                $projetoId,
                $mesAno,
                $coordenadorId,
                'colaboradores.id',
                $limitarAoHoje,
            ))
            ->groupBy('colaboradores.id', 'colaboradores.nome', 'colaboradores.remuneracao')
            ->orderBy('colaboradores.nome')
            ->select(['colaboradores.id', 'colaboradores.nome', 'colaboradores.remuneracao'])
            ->selectRaw('COUNT(DISTINCT atividades.projeto_id) as total_projetos')
            ->selectRaw('COALESCE(SUM(atividades.extensao_desenho), 0) as total_extensao_desenho')
            ->selectRaw('COALESCE(SUM(atividades.extensao_projeto), 0) as total_extensao_projeto')
            ->selectRaw('COALESCE(SUM(atividades.postes_desenhados), 0) as total_postes_desenhados')
            ->selectRaw('COALESCE(SUM(atividades.postes_projetados), 0) as total_postes_projetados')
            ->selectRaw("COALESCE(SUM(CASE WHEN atividades.tipo_projeto = 'PROJ' THEN 0 ELSE atividades.postes_desenhados END), 0) as total_postes_desenho_cad")
            ->selectRaw("COALESCE(SUM(CASE WHEN atividades.tipo_projeto = 'PROJ' THEN 0 ELSE atividades.postes_projetados END), 0) as total_postes_projeto_cad")
            ->selectRaw("COALESCE(SUM(CASE WHEN atividades.tipo_projeto = 'PROJ' THEN atividades.postes_projetados ELSE 0 END), 0) as total_postes_projetados_proj")
            ->selectRaw('COALESCE(SUM(COALESCE(atividades.duracao_minutos, 0) * 60), 0) as total_segundos')
            ->get();
    }

    /**
     * @return Collection<int, object{nome: string, total: int, acimaDaMeta: bool|null}>
     */
    public function producaoDashboard(?string $mesAno = null): Collection
    {
        $aplicaCorMeta = $mesAno !== null;
        $calculadoraBonus = app(BonusColaboradorCalculator::class);

        return (new Colaborador)->newModelQuery()
            ->join('atividades', 'atividades.colaborador_id', '=', 'colaboradores.id')
            ->join('projetos', 'projetos.id', '=', 'atividades.projeto_id')
            ->whereNull('atividades.deleted_at')
            ->whereNull('projetos.deleted_at')
            ->whereIn('atividades.tipo_projeto', [TipoProjetoAtividade::Cad->value, TipoProjetoAtividade::Proj->value])
            ->whereNotNull('atividades.data_conclusao')
            ->tap(fn (Builder $query) => $this->aplicarCompetenciaDashboard($query, $mesAno))
            ->groupBy('colaboradores.id', 'colaboradores.nome')
            ->havingRaw('COALESCE(SUM(atividades.postes_projetados), 0) > 0')
            ->orderByDesc('total')
            ->orderBy('colaboradores.nome')
            ->select(['colaboradores.nome'])
            ->selectRaw('COALESCE(SUM(atividades.postes_projetados), 0) as total')
            ->selectRaw('COALESCE(SUM(CASE WHEN atividades.tipo_projeto = ? THEN atividades.postes_projetados ELSE 0 END), 0) as total_cad', [TipoProjetoAtividade::Cad->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN atividades.tipo_projeto = ? THEN atividades.postes_projetados ELSE 0 END), 0) as total_proj', [TipoProjetoAtividade::Proj->value])
            ->get()
            ->map(function (Colaborador $colaborador) use ($aplicaCorMeta, $calculadoraBonus): object {
                return (object) [
                    'nome' => $colaborador->nome,
                    'total' => (int) $colaborador->total,
                    'acimaDaMeta' => $aplicaCorMeta
                        ? $calculadoraBonus->metaAtingida(0, (int) $colaborador->total_cad, (int) $colaborador->total_proj)
                        : null,
                ];
            })
            ->values();
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
