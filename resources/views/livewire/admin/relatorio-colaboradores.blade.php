<div>
    <div class="card">
        @if ($this->atividadesNaoConcluidasCount > 0)
            <div class="mx-4 mt-4 rounded bg-warning/10 px-4 py-3 text-sm text-warning" role="alert">
                Você tem {{ $this->atividadesNaoConcluidasCount }} atividades não concluidas
            </div>
        @endif
        <div class="card-header flex flex-wrap items-center justify-between gap-3">
            <h6 class="card-title mb-0">Produtividade CLT</h6>
            <div class="flex flex-wrap items-center gap-2">
                @if ($this->podeExportarPlanilhaContabilidade)
                    <button
                        type="button"
                        wire:click="exportarPlanilhaContabilidade"
                        class="btn btn-sm bg-primary text-white"
                    >
                        Baixar planilha de contabilidade
                    </button>
                @endif
                @if ($this->podeExportarProdutividade)
                    <button
                        type="button"
                        wire:click="exportarProdutividade"
                        @disabled(blank($mesAno))
                        class="btn btn-sm bg-primary text-white disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Baixar XLSX
                    </button>
                @endif
            </div>
        </div>
        @include('livewire.admin.partials.filtros-produtividade')

        <div class="flex flex-col">
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-default-200">
                            <thead class="bg-default-150">
                                <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                                    <th class="px-3.5 py-3 text-start" scope="col">Colaborador</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Projetos</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Extensão total</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Desenho CAD</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Projeto CAD</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Projeto PROJ</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Postes Total</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Bônus</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Horas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->produtividadeColaboradores as $colaborador)
                                    @php
                                        $interval = \Carbon\CarbonInterval::seconds((int) $colaborador->total_segundos)->cascade();
                                    @endphp
                                    <tr wire:key="produtividade-colaborador-{{ $colaborador->id }}" class="text-default-800 font-normal text-sm whitespace-nowrap">
                                        <td class="px-3.5 py-3">
                                            <h6 class="mb-0.5 font-semibold text-default-800">{{ $colaborador->nome }}</h6>
                                        </td>
                                        <td class="px-3.5 py-3">{{ (int) $colaborador->total_projetos }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $colaborador->total_extensao_desenho + (int) $colaborador->total_extensao_projeto }}</td>
                                        <td class="px-3.5 py-3">{{ $colaborador->meta_desenho_cad }}</td>
                                        <td class="px-3.5 py-3">{{ $colaborador->meta_projeto_cad }}</td>
                                        <td class="px-3.5 py-3">{{ $colaborador->meta_proj }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $colaborador->total_postes }}</td>
                                        <td class="px-3.5 py-3">R$ {{ number_format((float) $colaborador->total_bonus, 2, ',', '.') }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $interval->totalHours }}h {{ $interval->minutes }}min</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-3.5 py-8 text-center text-default-500">
                                            Nenhum colaborador com atividades atribuídas foi encontrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p class="px-3.5 pb-3 text-sm text-primary">
        * a meta para Desenho CAD é de 400 postes, para Projeto CAD é de 300 postes, para Projeto PROJ é de 230 postes.
    </p>
</div>
