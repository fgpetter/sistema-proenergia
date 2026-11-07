<?php

namespace App\Livewire\Admin;

use App\Models\TabelaTarifasProdutividadePj;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ParametrosProdutividadePj extends Component
{
    /** @var array<string, string> */
    public array $valores = [];

    #[Locked]
    public string $competencia;

    public function mount(): void
    {
        Gate::authorize('manage-parametros-produtividade-pj');
        $this->competencia = now()->format('Y-m');
        $this->carregarValores();
    }

    public function updatedCompetencia(): void
    {
        Gate::authorize('manage-parametros-produtividade-pj');
        $this->competencia = now()->format('Y-m');
    }

    public function salvar(): void
    {
        Gate::authorize('manage-parametros-produtividade-pj');
        $this->validate([
            'competencia' => ['required', 'date_format:Y-m'],
            'valores' => ['required', 'array'],
        ]);

        if ($this->competencia !== now()->format('Y-m')) {
            throw ValidationException::withMessages(['competencia' => 'Somente a competência atual pode ser alterada.']);
        }

        $tarifas = [];
        foreach ($this->campos() as $campo => [$casas, $forcarZero]) {
            if ($forcarZero) {
                $tarifas[$campo] = 0;
                continue;
            }
            $entrada = $this->valores[$campo] ?? null;
            if (! is_string($entrada)) {
                throw ValidationException::withMessages(['valores.'.$campo => 'Informe um valor decimal válido.']);
            }
            $raw = trim(str_replace(',', '.', $entrada));
            if (! preg_match('/^\d{1,7}(?:\.\d{1,'.$casas.'})?$/', $raw)) {
                throw ValidationException::withMessages(['valores.'.$campo => 'Informe um valor não negativo com '.$casas.' casas decimais no máximo.']);
            }
            $partes = explode('.', $raw, 2);
            $tarifas[$campo] = ((int) $partes[0] * 10000) + (int) str_pad($partes[1] ?? '', 4, '0');
        }

        $inicio = $this->competencia.'-01';
        DB::table('tabelas_tarifas_produtividade_pj')->upsert([[
            'competencia_inicio' => $inicio,
            'tarifas' => json_encode($tarifas, JSON_THROW_ON_ERROR),
            'salvo_por' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]], ['competencia_inicio'], ['tarifas', 'salvo_por', 'updated_at']);

        session()->flash('status', 'Parâmetros salvos para '.date('m/Y', strtotime($inicio)).'.');
        $this->carregarValores();
    }

    /** @return array<string, array{int, bool}> */
    private function campos(): array
    {
        return [
            'desenho_fixo' => [2, false], 'desenho_variavel' => [4, false],
            'projeto_1_10_fixo' => [2, false], 'projeto_1_10_variavel' => [4, false],
            'projeto_11_20_fixo' => [2, false], 'projeto_11_20_variavel' => [4, false],
            'projeto_21_mais_fixo' => [2, false], 'projeto_21_mais_variavel' => [4, false],
            'proj_1_10_fixo' => [2, false], 'proj_1_10_variavel' => [4, false],
            'proj_11_20_fixo' => [2, false], 'proj_11_20_variavel' => [4, false],
            'proj_21_mais_fixo' => [2, false], 'proj_21_mais_variavel' => [4, false],
            'relatorio_fixo' => [2, false], 'relatorio_variavel' => [4, true],
            'analise_proj_fixo' => [2, false], 'analise_proj_variavel' => [4, true],
        ];
    }

    private function carregarValores(): void
    {
        $registro = TabelaTarifasProdutividadePj::vigenteEm($this->competencia);
        foreach ($this->campos() as $campo => [$casas]) {
            $unidades = (int) ($registro?->tarifas[$campo] ?? 0);
            $this->valores[$campo] = number_format($unidades / 10000, $casas, ',', '');
        }
    }

    public function render(): View
    {
        Gate::authorize('manage-parametros-produtividade-pj');

        return view('livewire.admin.parametros-produtividade-pj');
    }
}
