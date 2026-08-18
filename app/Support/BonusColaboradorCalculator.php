<?php

namespace App\Support;

use App\Enums\TipoProjetoAtividade;
use App\Models\Colaborador;
use Illuminate\Support\Collection;

class BonusColaboradorCalculator
{
    private const LIMITE_POSTES_DESENHO_CAD = 400;

    private const LIMITE_POSTES_PROJETO_CAD = 300;

    private const LIMITE_POSTES_PROJ = 230;

    private const MULTIPLICADOR_BONUS = 2.0;

    private const BONUS_FIXO_META = 300.0;

    private const PERCENTUAL_TETO_BONUS = 0.70;

    /**
     * @param  iterable<int, array{tipo_projeto?: string|TipoProjetoAtividade|null, postes_desenhados?: int|float|string|null, postes_projetados?: int|float|string|null}|object>  $atividades
     */
    public function calcularDeAtividades(iterable $atividades): float
    {
        $postesDesenhoCad = 0.0;
        $postesProjetoCad = 0.0;
        $postesProjetoProj = 0.0;

        foreach ($atividades as $atividade) {
            $tipoProjeto = $this->resolveTipoProjeto($atividade);
            $postesProjetados = (float) data_get($atividade, 'postes_projetados', 0);
            $postesDesenhados = (float) data_get($atividade, 'postes_desenhados', 0);

            if ($tipoProjeto === TipoProjetoAtividade::Proj) {
                $postesProjetoProj += $postesProjetados;

                continue;
            }

            $postesDesenhoCad += $postesDesenhados;
            $postesProjetoCad += $postesProjetados;
        }

        return $this->calcular(
            postesDesenhoCad: $postesDesenhoCad,
            postesProjetoCad: $postesProjetoCad,
            postesProjetoProj: $postesProjetoProj,
        );
    }

    public function calcular(
        int|float $postesDesenhoCad = 0,
        int|float $postesProjetoCad = 0,
        int|float $postesProjetoProj = 0,
    ): float {
        $excedenteDesenhoCad = max(0, $postesDesenhoCad - self::LIMITE_POSTES_DESENHO_CAD);
        $excedenteProjetoCad = max(0, $postesProjetoCad - self::LIMITE_POSTES_PROJETO_CAD);
        $excedenteProj = max(0, $postesProjetoProj - self::LIMITE_POSTES_PROJ);
        $bonusFixo = $this->metaAtingida($postesDesenhoCad, $postesProjetoCad, $postesProjetoProj)
            ? self::BONUS_FIXO_META
            : 0.0;

        return $bonusFixo + (($excedenteDesenhoCad + $excedenteProjetoCad + $excedenteProj) * self::MULTIPLICADOR_BONUS);
    }

    public function metaAtingida(
        int|float $postesDesenhoCad,
        int|float $postesProjetoCad,
        int|float $postesProjetoProj,
    ): bool {
        return $postesDesenhoCad >= self::LIMITE_POSTES_DESENHO_CAD
            || $postesProjetoCad >= self::LIMITE_POSTES_PROJETO_CAD
            || $postesProjetoProj >= self::LIMITE_POSTES_PROJ;
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
        $postesDesenhoCad = (float) ($colaborador->total_postes_desenho_cad ?? 0);
        $postesProjetoCad = (float) ($colaborador->total_postes_projeto_cad ?? 0);
        $postesProj = (float) ($colaborador->total_postes_projetados_proj ?? 0);

        $bonusBruto = $this->calcular(
            postesDesenhoCad: $postesDesenhoCad,
            postesProjetoCad: $postesProjetoCad,
            postesProjetoProj: $postesProj,
        );

        $colaborador->total_bonus = $this->aplicarTeto(
            $bonusBruto,
            $colaborador->remuneracao,
        );
        $colaborador->meta_desenho_cad = $this->formatarMetaDesenhoCad($postesDesenhoCad);
        $colaborador->meta_projeto_cad = $this->formatarMetaProjetoCad($postesProjetoCad);
        $colaborador->meta_proj = $this->formatarMetaProj($postesProj);
        $colaborador->total_postes = (int) $postesDesenhoCad + (int) $postesProjetoCad + (int) $postesProj;

        return $colaborador;
    }

    public function formatarMetaDesenhoCad(int|float $postesDesenhoCad): string
    {
        return sprintf('%d / %d', (int) $postesDesenhoCad, self::LIMITE_POSTES_DESENHO_CAD);
    }

    public function formatarMetaProjetoCad(int|float $postesProjetoCad): string
    {
        return sprintf('%d / %d', (int) $postesProjetoCad, self::LIMITE_POSTES_PROJETO_CAD);
    }

    public function formatarMetaProj(int|float $postesProjetoProj): string
    {
        return sprintf('%d / %d', (int) $postesProjetoProj, self::LIMITE_POSTES_PROJ);
    }

    public function formatarMeta(
        int|float $postesDesenhoCad,
        int|float $postesProjetoCad,
        int|float $postesProjetoProj,
    ): string {
        return sprintf(
            '%s - %s - %s',
            $this->formatarMetaDesenhoCad($postesDesenhoCad),
            $this->formatarMetaProjetoCad($postesProjetoCad),
            $this->formatarMetaProj($postesProjetoProj),
        );
    }

    /**
     * Soma o bônus por colaborador a partir de todas as atividades da competência filtrada.
     *
     * @param  Collection<int, object|array<string, mixed>>  $atividades
     * @return Collection<int|string, float>
     */
    public function somarPorColaborador(Collection $atividades): Collection
    {
        return $atividades
            ->groupBy(fn ($atividade) => data_get($atividade, 'colaborador_id'))
            ->map(fn (Collection $atividadesDoColaborador): float => $this->calcularDeAtividades($atividadesDoColaborador));
    }

    private function resolveTipoProjeto(mixed $atividade): TipoProjetoAtividade
    {
        $tipo = data_get($atividade, 'tipo_projeto');

        if ($tipo instanceof TipoProjetoAtividade) {
            return $tipo;
        }

        if (is_string($tipo) && $tipo !== '') {
            return TipoProjetoAtividade::from($tipo);
        }

        return TipoProjetoAtividade::Cad;
    }
}
