<?php

namespace App\Support;

use App\Enums\TipoProjetoParte;
use Illuminate\Support\Collection;

class BonusColaboradorCalculator
{
    private const LIMITE_POSTES_DESENHADOS = 280;

    private const LIMITE_POSTES_PROJETADOS = 220;

    private const MULTIPLICADOR_PROJ = 1.375;

    private const MULTIPLICADOR_BONUS = 1.82;

    /**
     * @param  iterable<int, array{tipo_projeto?: string|TipoProjetoParte|null, postes_desenhados?: int|float|string|null, postes_projetados?: int|float|string|null}|object>  $partes
     */
    public function calcularDePartes(iterable $partes): float
    {
        $postesDesenhados = 0;
        $postesProjetadosCad = 0.0;
        $postesProjetadosProj = 0.0;

        foreach ($partes as $parte) {
            $tipoProjeto = $this->resolveTipoProjeto($parte);
            $postesDesenhados += (int) data_get($parte, 'postes_desenhados', 0);
            $postesProjetados = (float) data_get($parte, 'postes_projetados', 0);

            if ($tipoProjeto === TipoProjetoParte::Proj) {
                $postesProjetadosProj += $postesProjetados;
            } else {
                $postesProjetadosCad += $postesProjetados;
            }
        }

        return $this->calcular(
            $postesDesenhados,
            $postesProjetadosCad,
            $postesProjetadosProj
        );
    }

    public function calcular(
        int|float $postesDesenhados,
        int|float $postesProjetadosCad,
        int|float $postesProjetadosProj
    ): float {
        $base = ($postesDesenhados - self::LIMITE_POSTES_DESENHADOS)
            + ($postesProjetadosCad + ($postesProjetadosProj * self::MULTIPLICADOR_PROJ) - self::LIMITE_POSTES_PROJETADOS);

        return max(0, $base) * self::MULTIPLICADOR_BONUS;
    }

    /**
     * Soma o bônus por colaborador a partir de todas as partes da competência filtrada.
     *
     * @param  Collection<int, object|array<string, mixed>>  $partes
     * @return Collection<int|string, float>
     */
    public function somarPorColaborador(Collection $partes): Collection
    {
        return $partes
            ->groupBy(fn ($parte) => data_get($parte, 'colaborador_id'))
            ->map(fn (Collection $partesDoColaborador): float => $this->calcularDePartes($partesDoColaborador));
    }

    private function resolveTipoProjeto(mixed $parte): TipoProjetoParte
    {
        $tipo = data_get($parte, 'tipo_projeto');

        if ($tipo instanceof TipoProjetoParte) {
            return $tipo;
        }

        if (is_string($tipo) && $tipo !== '') {
            return TipoProjetoParte::from($tipo);
        }

        return TipoProjetoParte::Cad;
    }
}
