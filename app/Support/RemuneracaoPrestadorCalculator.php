<?php

namespace App\Support;

use App\Enums\TipoProjetoAtividade;
use App\Models\Atividade;

class RemuneracaoPrestadorCalculator
{
    /** @param array<string, int> $tarifas
     *  @return array{desenho:int, projeto:int, proj:int, relatorio:int, analise_proj:int, faixa:?string}
     */
    public function calcular(Atividade $atividade, array $tarifas): array
    {
        $tipo = $atividade->tipo_projeto ?? TipoProjetoAtividade::Cad;
        $metrosProjeto = (int) ($atividade->extensao_projeto ?? 0);
        $faixa = $metrosProjeto > 0 ? $this->faixa((int) ($atividade->postes_projetados ?? 0)) : null;
        $projetoPrefixo = $tipo === TipoProjetoAtividade::Proj ? 'proj' : 'projeto';
        $valorProjeto = 0;

        if ($metrosProjeto > 0) {
            $valorProjeto = $this->valor($tarifas, $projetoPrefixo.'_'.$faixa, $metrosProjeto);
        }

        return [
            'desenho' => (int) ($atividade->extensao_desenho ?? 0) > 0
                ? $this->valor($tarifas, 'desenho', (int) $atividade->extensao_desenho) : 0,
            'projeto' => $tipo === TipoProjetoAtividade::Cad ? $valorProjeto : 0,
            'proj' => $tipo === TipoProjetoAtividade::Proj ? $valorProjeto : 0,
            'relatorio' => (bool) ($atividade->relatorio_realizado ?? false) ? (int) ($tarifas['relatorio_fixo'] ?? 0) : 0,
            'analise_proj' => (bool) ($atividade->analise_proj_realizada ?? false) ? (int) ($tarifas['analise_proj_fixo'] ?? 0) : 0,
            'faixa' => $faixa,
        ];
    }

    private function faixa(int $postes): string
    {
        return match (true) {
            $postes <= 10 => '1_10',
            $postes <= 20 => '11_20',
            default => '21_mais',
        };
    }

    /** @param array<string, int> $tarifas */
    private function valor(array $tarifas, string $prefixo, int $metros): int
    {
        return (int) ($tarifas[$prefixo.'_fixo'] ?? 0) + ($metros * (int) ($tarifas[$prefixo.'_variavel'] ?? 0));
    }
}
