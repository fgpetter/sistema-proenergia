<?php

namespace App\Livewire\Admin;

use App\Enums\TipoContrato;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\TabelaTarifasProdutividadePj;
use App\Support\RemuneracaoPrestadorCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

class RelatorioProdutividadePj extends Component
{
    #[Url(as: 'projeto')]
    public ?int $projetoId = null;

    #[Url(as: 'mes')]
    public ?string $mesAno = null;

    #[Url(as: 'coord')]
    public ?int $coordenadorId = null;

    #[Locked]
    public ?int $prestadorDetalhadoId = null;

    public function mount(): void
    {
        Gate::authorize('view-relatorio-produtividade-pj');
        $this->mesAno ??= now()->format('Y-m');
        $this->validarCompetencia();
    }

    public function updatedMesAno(): void
    {
        Gate::authorize('view-relatorio-produtividade-pj');
        $this->validarCompetencia();
        unset($this->relatorio);
    }

    public function updatedProjetoId(): void
    {
        Gate::authorize('view-relatorio-produtividade-pj');
        unset($this->relatorio);
    }

    public function updatedCoordenadorId(): void
    {
        Gate::authorize('view-relatorio-produtividade-pj');
        unset($this->relatorio);
    }

    public function abrirDetalhes(int $prestadorId): bool
    {
        Gate::authorize('view-relatorio-produtividade-pj');

        $this->prestadorDetalhadoId = $this->relatorio->has($prestadorId) ? $prestadorId : null;

        return $this->prestadorDetalhadoId !== null;
    }

    #[Computed]
    public function competenciasDisponiveis(): Collection
    {
        $mes = CarbonImmutable::now()->startOfMonth();
        $inicio = $this->inicioTarifas() ?: $mes->toDateString();
        $competencias = collect([$mes->format('Y-m') => 'Mês atual até hoje']);

        for ($mes = $mes->subMonth(); $mes->toDateString() >= $inicio; $mes = $mes->subMonth()) {
            $rotulo = ucfirst($mes->locale('pt_BR')->translatedFormat('F - Y'));
            $competencias->put($mes->format('Y-m'), $rotulo);
        }

        return $competencias;
    }

    #[Computed]
    public function projetos(): Collection
    {
        return $this->atividadesElegiveis()
            ->join('projetos', 'projetos.id', '=', 'atividades.projeto_id')
            ->whereNull('projetos.deleted_at')
            ->whereDate('atividades.data_conclusao', '>=', $this->inicioTarifas())
            ->select('projetos.id', 'projetos.nome')->distinct()->orderBy('projetos.nome')->get();
    }

    #[Computed]
    public function coordenadores(): Collection
    {
        return $this->atividadesElegiveis()
            ->join('projetos', 'projetos.id', '=', 'atividades.projeto_id')
            ->whereNull('projetos.deleted_at')
            ->whereDate('atividades.data_conclusao', '>=', $this->inicioTarifas())
            ->whereNotNull('projetos.colaborador_responsavel_id')
            ->join('colaboradores', 'colaboradores.id', '=', 'projetos.colaborador_responsavel_id')
            ->select('colaboradores.id', 'colaboradores.nome')->distinct()->orderBy('colaboradores.nome')->get();
    }

    #[Computed]
    public function relatorio(): Collection
    {
        Gate::authorize('view-relatorio-produtividade-pj');
        $this->validarCompetencia();

        $tabela = TabelaTarifasProdutividadePj::vigenteEm($this->mesAno);
        if ($tabela === null) {
            return collect();
        }

        $limitando = $this->mesAno === now()->format('Y-m');
        $atividades = (new Atividade)->listarAtividadesRelatorio(
            colaboradorId: $this->colaboradorIdEscopo(),
            projetoId: $this->projetoId,
            mesAno: $this->mesAno,
            coordenadorId: $this->coordenadorId,
            limitarAoHoje: $limitando,
            contrato: TipoContrato::PJ,
        )->load('colaborador');

        $calculator = app(RemuneracaoPrestadorCalculator::class);

        return $atividades->map(function (Atividade $atividade) use ($calculator, $tabela): Atividade {
            $atividade->remuneracao_pj = $calculator->calcular($atividade, $tabela->tarifas ?? []);

            return $atividade;
        })->groupBy('colaborador_id')->map(function (Collection $items): array {
            $colaborador = $items->first()->colaborador;
            $projetos = $items->groupBy('projeto_id')->map(fn (Collection $projetoAtividades): array => [
                'projeto' => $projetoAtividades->first()->projeto,
                'atividades' => $projetoAtividades,
            ]);
            $totais = ['desenho' => 0, 'projeto' => 0, 'proj' => 0, 'relatorio' => 0, 'analise_proj' => 0];
            foreach ($items as $atividade) {
                foreach ($totais as $campo => $valor) {
                    $totais[$campo] += (int) ($atividade->remuneracao_pj[$campo] ?? 0);
                }
            }
            $totais['total'] = array_sum($totais);

            return ['colaborador' => $colaborador, 'projetos' => $projetos, 'totais' => $totais];
        });
    }

    private function atividadesElegiveis(): \Illuminate\Database\Eloquent\Builder
    {
        Gate::authorize('view-relatorio-produtividade-pj');
        $query = Atividade::query()
            ->whereHas('colaborador', fn ($builder) => $builder->where('contrato', TipoContrato::PJ->value))
            ->whereNotNull('data_conclusao')->whereNull('em_revisao');

        if (($colaboradorId = $this->colaboradorIdEscopo()) !== null) {
            $query->where('colaborador_id', $colaboradorId);
        }

        return $query;
    }

    private function colaboradorIdEscopo(): ?int
    {
        $user = auth()->user();
        if ($user === null || $user->isAdminOrSuperAdmin() || $user->isCoordenador()) {
            return null;
        }

        return $user->colaborador?->id ?? -1;
    }

    private function validarCompetencia(): void
    {
        $mesAtual = now()->format('Y-m');
        $formatoValido = is_string($this->mesAno) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $this->mesAno) === 1;
        $inicial = TabelaTarifasProdutividadePj::query()->min('competencia_inicio');
        if (! $formatoValido || $this->mesAno > $mesAtual || $inicial === null || $this->mesAno < substr((string) $inicial, 0, 7)) {
            $this->mesAno = $mesAtual;
        }
    }

    private function inicioTarifas(): string
    {
        return (string) TabelaTarifasProdutividadePj::query()->min('competencia_inicio');
    }

    public function formatarDinheiro(int $unidades): string
    {
        return 'R$ '.number_format($unidades / 10000, 2, ',', '.');
    }

    public function faixaLabel(?string $faixa): string
    {
        return match ($faixa) {
            '1_10' => 'Até 10 postes', '11_20' => '11 a 20 postes', '21_mais' => 'Mais de 20 postes', default => '—',
        };
    }

    public function render(): View
    {
        Gate::authorize('view-relatorio-produtividade-pj');

        return view('livewire.admin.relatorio-produtividade-pj');
    }
}
