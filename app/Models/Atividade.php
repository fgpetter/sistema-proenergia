<?php

namespace App\Models;

use App\Enums\TipoProjetoAtividade;
use Carbon\Carbon;
use Database\Factories\AtividadeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Atividade extends Model
{
    /** @use HasFactory<AtividadeFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'projeto_id',
        'nome',
        'colaborador_id',
        'extensao_desenho',
        'extensao_projeto',
        'postes_desenhados',
        'postes_projetados',
        'tipo_projeto',
        'duracao_minutos',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'extensao_desenho' => 'integer',
            'extensao_projeto' => 'integer',
            'postes_desenhados' => 'integer',
            'postes_projetados' => 'integer',
            'tipo_projeto' => TipoProjetoAtividade::class,
            'duracao_minutos' => 'integer',
        ];
    }

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(Projeto::class);
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class)->withTrashed();
    }

    public function getExtensaoTotalAttribute(): int
    {
        return $this->extensao_desenho + $this->extensao_projeto;
    }

    public function getTotalPostesAttribute(): int
    {
        return $this->postes_desenhados + $this->postes_projetados;
    }

    /**
     * @return Collection<int, object{semana: int, rotulo: string, total: int}>
     */
    public function evolucaoSemanalPostes(): Collection
    {
        $inicio = now()->copy()->startOfMonth();
        $fim = now()->copy()->endOfMonth();
        $totalSemanas = (int) intdiv($inicio->daysInMonth - 1, 7) + 1;
        $totaisPorBloco = array_fill(1, $totalSemanas, 0);

        $linhas = Atividade::query()
            ->whereIn('atividades.tipo_projeto', [TipoProjetoAtividade::Cad->value, TipoProjetoAtividade::Proj->value])
            ->whereBetween('atividades.created_at', [$inicio, $fim])
            ->toBase()
            ->get(['atividades.created_at', 'atividades.postes_projetados']);

        foreach ($linhas as $linha) {
            $bloco = (int) intdiv(Carbon::parse($linha->created_at)->day - 1, 7) + 1;
            $totaisPorBloco[$bloco] += (int) $linha->postes_projetados;
        }

        $acumulado = 0;

        return collect(range(1, $totalSemanas))
            ->map(function (int $semana) use (&$acumulado, $totaisPorBloco): object {
                $acumulado += $totaisPorBloco[$semana];

                return (object) ['semana' => $semana, 'rotulo' => (string) $semana, 'total' => $acumulado];
            });
    }

    /**
     * @return Collection<int, Atividade>
     */
    public function listarAtividadesRelatorio(
        ?int $colaboradorId = null,
        ?int $projetoId = null,
        ?string $mesAno = null,
        ?int $coordenadorId = null,
        bool $limitarAoHoje = false,
    ): Collection {
        return Atividade::query()
            ->select('atividades.*')
            ->join('projetos', 'projetos.id', '=', 'atividades.projeto_id')
            ->whereNull('projetos.deleted_at')
            ->with('projeto')
            ->tap(fn (Builder $query) => $this->aplicarFiltrosRelatorio(
                $query,
                $colaboradorId,
                $projetoId,
                $mesAno,
                $coordenadorId,
                'atividades.colaborador_id',
                $limitarAoHoje,
            ))
            ->orderBy('projetos.created_at')
            ->orderBy('atividades.nome')
            ->get();
    }

    public function aplicarFiltrosRelatorio(
        Builder $query,
        ?int $colaboradorId,
        ?int $projetoId,
        ?string $mesAno,
        ?int $coordenadorId,
        string $colaboradorColumn,
        bool $limitarAoHoje = false,
    ): void {
        $query
            ->when($colaboradorId !== null, function (Builder $query) use ($colaboradorId, $colaboradorColumn): void {
                $query->where($colaboradorColumn, $colaboradorId);
            })
            ->when($projetoId, function (Builder $query) use ($projetoId): void {
                $query->where('atividades.projeto_id', $projetoId);
            })
            ->when($mesAno, function (Builder $query) use ($mesAno, $limitarAoHoje): void {
                $inicio = Carbon::createFromFormat('Y-m', $mesAno)->startOfMonth();
                $fim = $limitarAoHoje ? now()->endOfDay() : $inicio->copy()->endOfMonth();

                $query->whereBetween('projetos.created_at', [$inicio, $fim]);
            })
            ->when($coordenadorId, function (Builder $query) use ($coordenadorId): void {
                $query->where('projetos.colaborador_responsavel_id', $coordenadorId);
            });
    }
}
