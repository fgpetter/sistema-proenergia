<div>
    <div class="grid lg:grid-cols-3 grid-cols-1 gap-5 mb-5">
        <div class="lg:col-span-1 flex flex-col gap-5">
            <div>
                <label class="mb-1 block text-sm font-medium text-default-700">Mês/Ano</label>
                <select wire:model.live="mesAno" class="form-input form-input-sm w-full">
                    <option value="todas">Todas as competências</option>
                    @foreach ($this->competenciasDisponiveis as $valor => $rotulo)
                        <option value="{{ $valor }}">{{ $rotulo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div class="card bg-blue-100">
                    <div class="card-body">
                        <p class="text-base text-default-500 font-medium">Numero total de projetos</p>
                        <h5 class="text-3xl font-medium mt-4">{{ $this->totais->totalProjetos }}</h5>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body bg-orange-100">
                        <p class="text-base text-default-500 font-medium">Extensao de projeto</p>
                        <h5 class="text-3xl font-medium mt-4">{{ (int) $this->totais->totalExtensaoProjeto }}</h5>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body bg-rose-100">
                        <p class="text-base text-default-500 font-medium">Postes projetados</p>
                        <h5 class="text-3xl font-medium mt-4">{{ (int) $this->totais->totalPostesProjetados }}</h5>
                    </div>
                </div>

                <div class="card bg-green-100">
                    <div class="card-body">
                        <p class="text-base text-default-500 font-medium ">Total de Horas reportadas</p>
                        @php
                            $intervalTotais = \Carbon\CarbonInterval::seconds((int) $this->totais->totalSegundos)->cascade();
                        @endphp
                        <h5 class="text-3xl font-medium mt-4">{{ (int) $intervalTotais->totalHours }}h {{ $intervalTotais->minutes }}min</h5>
                    </div>
                </div>
            </div>
        </div>

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
                                        <td class="px-3.5 py-3">{{ (int) $projeto->total_extensao_projeto }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $projeto->total_postes_projetados }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $intervalProjeto->totalHours }}h {{ $intervalProjeto->minutes }}min</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3.5 py-8 text-center text-default-500">
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
