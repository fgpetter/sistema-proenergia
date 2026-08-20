<div>
    <div class="flex items-center flex-wrap gap-2 mb-4 print:hidden">
        <h4 class="text-default-900 text-lg font-semibold">Dashboard</h4>
        <div class="ms-auto">
            <label class="mb-1 block text-sm font-medium text-default-700">Mês/Ano</label>
            <select wire:model.live="mesAno" class="form-input form-input-sm w-full min-w-48">
                <option value="todas">Todas as competências</option>
                @foreach ($this->competenciasDisponiveis as $valor => $rotulo)
                    <option value="{{ $valor }}">{{ $rotulo }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-5 mb-5">
        <div class="card bg-blue-100">
            <div class="card-body">
                <p class="text-base text-default-500 font-medium">Total de projetos</p>
                <h5 class="text-[1.41rem] font-medium mt-4">{{ $this->totais->totalProjetos }}</h5>
            </div>
        </div>

        <div class="card">
            <div class="card-body bg-orange-100">
                <p class="text-base text-default-500 font-medium">Extensão total</p>
                <h5 class="text-[1.41rem] font-medium mt-4">{{ (int) $this->totais->totalExtensaoProjeto }}</h5>
            </div>
        </div>

        <div class="card">
            <div class="card-body bg-rose-100">
                <p class="text-base text-default-500 font-medium">Postes projetados</p>
                <h5 class="text-[1.41rem] font-medium mt-4">{{ (int) $this->totais->totalPostesProjetados }}</h5>
            </div>
        </div>

        <div class="card bg-green-100">
            <div class="card-body">
                <p class="text-base text-default-500 font-medium">Total de horas</p>
                @php
                    $intervalTotais = \Carbon\CarbonInterval::seconds((int) $this->totais->totalSegundos)->cascade();
                @endphp
                <h5 class="text-[1.41rem] font-medium mt-4">{{ (int) $intervalTotais->totalHours }}h {{ $intervalTotais->minutes }}min</h5>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <p class="text-base text-default-500 font-medium">Extensão por projeto</p>
                <h5 class="text-[1.41rem] font-medium mt-4">{{ number_format($this->totais->mediaExtensaoPorProjeto, 1, ',', '.') }}</h5>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <p class="text-base text-default-500 font-medium">Postes por projeto</p>
                <h5 class="text-[1.41rem] font-medium mt-4">{{ number_format($this->totais->mediaPostesPorProjeto, 1, ',', '.') }}</h5>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <p class="text-base text-default-500 font-medium">Horas por projeto</p>
                @php
                    $intervalMediaHoras = \Carbon\CarbonInterval::seconds((int) round($this->totais->mediaSegundosPorProjeto))->cascade();
                @endphp
                <h5 class="text-[1.41rem] font-medium mt-4">{{ (int) $intervalMediaHoras->totalHours }}h {{ $intervalMediaHoras->minutes }}min</h5>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <p class="text-base text-default-500 font-medium">Vão médio projetado</p>
                <h5 class="text-[1.41rem] font-medium mt-4">{{ number_format($this->totais->vaoMedioProjetado, 1, ',', '.') }}</h5>
            </div>
        </div>
    </div>

    <div class="mb-5">
        <div class="grid lg:grid-cols-2 grid-cols-1 gap-5"
             id="analise-grafica-payloads"
             data-producao='@json($this->graficoProducaoPayload)'
             data-evolucao='@json($this->graficoEvolucaoPayload)'>
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Produção por colaborador (postes)</h6>
                </div>
                <div class="card-body">
                    <p id="chart-producao-colaborador-vazio" @class([
                        'text-sm text-default-500 text-center py-16',
                        'hidden' => $this->producaoPorColaborador->isNotEmpty(),
                    ])>
                        Nenhum Projeto CAD encontrado para o filtro selecionado.
                    </p>
                    <div id="chart-producao-colaborador" @class(['hidden' => $this->producaoPorColaborador->isEmpty()]) wire:ignore></div>
                </div>
                <div class="card-footer">
                    <p class="text-sm text-default-500">
                        @if ($this->mesAnoFiltro)
                            Ranking da competência, destacando quem já ultrapassou a Meta Projeto CAD
                        @else
                            Ranking agregado de todas as competências (sem destaque de meta)
                        @endif
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Evolução semanal de postes</h6>
                </div>
                <div class="card-body">
                    <div id="chart-evolucao-semanal" wire:ignore></div>
                </div>
                <div class="card-footer">
                    <p class="text-sm text-default-500">
                        Projeto CAD acumulado em {{ $this->rotuloMesAtual }}, do dia 1 até o fim de cada semana
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 mb-5">
        <livewire:painel.performance-colaboradores :mes-ano="$this->mesAnoFiltro" :key="$this->mesAno" />
    </div>

    <div class="grid grid-cols-1 gap-5">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Estatisticas de projetos</h6>
            </div>
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-default-200">
                            <thead class="bg-default-150">
                                <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                                    <th class="px-3.5 py-3 text-start" scope="col">Nome</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Coordenador</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Extensao de projeto</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Postes projetados</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Total de Horas reportadas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-default-200">
                                @forelse ($this->estatisticasProjetos as $projeto)
                                    @php
                                        $intervalProjeto = \Carbon\CarbonInterval::seconds((int) $projeto->total_segundos)->cascade();
                                    @endphp
                                    <tr wire:key="estatistica-projeto-{{ $projeto->id }}" class="text-default-800 font-normal text-sm whitespace-nowrap">
                                        <td class="px-3.5 py-3">
                                            <div class="hs-tooltip [--placement:right]">
                                                    <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-30 py-1 px-2 bg-default-900 text-xs font-medium text-default-100 rounded-md shadow-2xs" role="tooltip">
                                                        {{ $projeto->nome }}
                                                    </span>
                                                {{ \Illuminate\Support\Str::limit($projeto->nome, 50) }}
                                            </div>
                                        </td>
                                        <td class="px-3.5 py-3">{{ $projeto->coordenador ?? '—' }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $projeto->total_extensao_projeto }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $projeto->total_postes_projetados }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $intervalProjeto->totalHours }}h {{ $intervalProjeto->minutes }}min</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3.5 py-8 text-center text-default-500">
                                            Nenhum projeto cadastrado foi encontrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @if ($this->estatisticasProjetos->hasPages())
                <div class="card-footer">
                    <p class="text-default-500 text-sm">
                        Exibindo <b>{{ $this->estatisticasProjetos->firstItem() ?? 0 }}</b> a <b>{{ $this->estatisticasProjetos->lastItem() ?? 0 }}</b> de <b>{{ $this->estatisticasProjetos->total() }}</b> resultados
                    </p>
                    <nav aria-label="Pagination" class="flex items-center gap-2">
                        @if ($this->estatisticasProjetos->onFirstPage())
                            <button disabled class="btn btn-sm border bg-transparent border-default-200 text-default-400 cursor-not-allowed" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="15 18 9 12 15 6"/></svg> Anterior
                            </button>
                        @else
                            <button wire:click="previousPage" class="btn btn-sm border bg-transparent border-default-200 text-default-600 hover:bg-primary/10 hover:text-primary hover:border-primary/10" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="15 18 9 12 15 6"/></svg> Anterior
                            </button>
                        @endif

                        @foreach ($this->estatisticasProjetos->getUrlRange(1, $this->estatisticasProjetos->lastPage()) as $page => $url)
                            @if ($page == $this->estatisticasProjetos->currentPage())
                                <button class="btn size-7.5 bg-primary text-white" type="button">{{ $page }}</button>
                            @else
                                <button wire:click="gotoPage({{ $page }})" class="btn size-7.5 bg-transparent border border-default-200 text-default-600 hover:bg-primary/10 hover:text-primary hover:border-primary/10" type="button">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach

                        @if ($this->estatisticasProjetos->hasMorePages())
                            <button wire:click="nextPage" class="btn btn-sm border bg-transparent border-default-200 text-default-600 hover:bg-primary/10 hover:text-primary hover:border-primary/10" type="button">
                                Próximo <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ms-1"><polyline points="9 18 15 12 9 6"/></svg>
                            </button>
                        @else
                            <button disabled class="btn btn-sm border bg-transparent border-default-200 text-default-400 cursor-not-allowed" type="button">
                                Próximo <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ms-1"><polyline points="9 18 15 12 9 6"/></svg>
                            </button>
                        @endif
                    </nav>
                </div>
            @endif
        </div>
    </div>
</div>
