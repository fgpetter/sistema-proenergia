<?php

namespace App\Support;

use App\Enums\TipoProjetoParte;
use App\Models\Colaborador;
use Illuminate\Support\Collection;

class BonusColaboradorCalculator
{
    private const LIMITE_POSTES_CAD = 300;

    private const LIMITE_POSTES_PROJ = 230;

    private const MULTIPLICADOR_BONUS = 2.0;

    private const BONUS_FIXO_META = 300.0;

    private const PERCENTUAL_TETO_BONUS = 0.70;

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
        $bonusFixo = $this->metaAtingida($postesProjetadosCad, $postesProjetadosProj)
            ? self::BONUS_FIXO_META
            : 0.0;

        return $bonusFixo + (($excedenteCad + $excedenteProj) * self::MULTIPLICADOR_BONUS);
    }

    public function metaAtingida(int|float $postesProjetadosCad, int|float $postesProjetadosProj): bool
    {
        return $postesProjetadosCad >= self::LIMITE_POSTES_CAD
            || $postesProjetadosProj >= self::LIMITE_POSTES_PROJ;
    }

    /**
     * Aplica o teto de 70% da remuneração bruta ao bônus.
     * Sem remuneração cadastrada, o bônus real é zero.
     */
    public function aplicarTeto(float $bonusBruto, ?int $remuneracaoCentavos): float
    {
        if ($remuneracaoCentavos === null) {
            return 0.0;
        }

        $teto = ($remuneracaoCentavos / 100) * self::PERCENTUAL_TETO_BONUS;

        return min($bonusBruto, $teto);
    }

    /**
     * Preenche meta, totais e bônus com teto a partir dos postes agregados.
     */
    public function enriquecerColaborador(Colaborador $colaborador): Colaborador
    {
        $postesCad = (float) ($colaborador->total_postes_projetados_cad ?? 0);
        $postesProj = (float) ($colaborador->total_postes_projetados_proj ?? 0);

        $bonusBruto = $this->calcular(
            postesProjetadosCad: $postesCad,
            postesProjetadosProj: $postesProj,
        );

        $colaborador->total_bonus = $this->aplicarTeto(
            $bonusBruto,
            $colaborador->remuneracao,
        );
        $colaborador->meta_cad = $this->formatarMetaCad($postesCad);
        $colaborador->meta_proj = $this->formatarMetaProj($postesProj);
        $colaborador->total_postes = (int) $postesCad + (int) $postesProj;

        return $colaborador;
    }

    public function formatarMetaCad(int|float $postesProjetadosCad): string
    {
        return sprintf('%d / %d', (int) $postesProjetadosCad, self::LIMITE_POSTES_CAD);
    }

    public function formatarMetaProj(int|float $postesProjetadosProj): string
    {
        return sprintf('%d / %d', (int) $postesProjetadosProj, self::LIMITE_POSTES_PROJ);
    }

    public function formatarMeta(int|float $postesProjetadosCad, int|float $postesProjetadosProj): string
    {
        return sprintf(
            '%s - %s',
            $this->formatarMetaCad($postesProjetadosCad),
            $this->formatarMetaProj($postesProjetadosProj),
        );
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
