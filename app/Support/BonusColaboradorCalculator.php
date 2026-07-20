<?php

namespace App\Support;

use App\Enums\TipoProjetoParte;
use Illuminate\Support\Collection;

class BonusColaboradorCalculator
{
    private const LIMITE_POSTES_CAD = 400;

    private const LIMITE_POSTES_PROJ = 230;

    private const MULTIPLICADOR_BONUS = 1.82;

    /**
     * @param  iterable<int, array{tipo_projeto?: string|TipoProjetoParte|null, postes_desenhados?: int|float|string|null, postes_projetados?: int|float|string|null}|object>  $partes
     */
    public function calcularDePartes(iterable $partes): float
    {
        $postesProjetadosCad = 0.0;
        $postesProjetadosProj = 0.0;

        foreach ($partes as $parte) {
            $tipoProjeto = $this->resolveTipoProjeto($parte);
            $postesProjetados = (float) data_get($parte, 'postes_projetados', 0);

            if ($tipoProjeto === TipoProjetoParte::Proj) {
                $postesProjetadosProj += $postesProjetados;
            } else {
                $postesProjetadosCad += $postesProjetados;
            }
        }

        return $this->calcular(
            postesProjetadosCad: $postesProjetadosCad,
            postesProjetadosProj: $postesProjetadosProj,
        );
    }

    public function calcular(
        int|float $postesDesenhados = 0,
        int|float $postesProjetadosCad = 0,
        int|float $postesProjetadosProj = 0,
    ): float {
        $excedenteCad = max(0, $postesProjetadosCad - self::LIMITE_POSTES_CAD);
        $excedenteProj = max(0, $postesProjetadosProj - self::LIMITE_POSTES_PROJ);

        return ($excedenteCad + $excedenteProj) * self::MULTIPLICADOR_BONUS;
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
