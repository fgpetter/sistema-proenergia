<div x-data>
    <div class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-3">
            <h6 class="card-title mb-0">Produtividade PJ</h6>
        </div>

        @include('livewire.admin.partials.filtros-produtividade', ['mostrarAtalhoMesAtual' => false])

        <div class="flex flex-col">
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-default-200">
                            <thead class="bg-default-150">
                                <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                                    <th class="px-3.5 py-3 text-start" scope="col">Prestador</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Desenho</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Projeto</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">PROJ</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Relatório</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Análise Proj</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Remuneração total</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->relatorio as $prestadorId => $registro)
                                    <tr wire:key="prestador-{{ $prestadorId }}" class="text-default-800 font-normal text-sm whitespace-nowrap">
                                        <td class="px-3.5 py-3">
                                            <h6 class="mb-0.5 font-semibold text-default-800">{{ $registro['colaborador']->nome }}</h6>
                                        </td>
                                        <td class="px-3.5 py-3">{{ $this->formatarDinheiro($registro['totais']['desenho']) }}</td>
                                        <td class="px-3.5 py-3">{{ $this->formatarDinheiro($registro['totais']['projeto']) }}</td>
                                        <td class="px-3.5 py-3">{{ $this->formatarDinheiro($registro['totais']['proj']) }}</td>
                                        <td class="px-3.5 py-3">{{ $this->formatarDinheiro($registro['totais']['relatorio']) }}</td>
                                        <td class="px-3.5 py-3">{{ $this->formatarDinheiro($registro['totais']['analise_proj']) }}</td>
                                        <td class="px-3.5 py-3 font-semibold">{{ $this->formatarDinheiro($registro['totais']['total']) }}</td>
                                        <td class="px-3.5 py-3">
                                            <button
                                                type="button"
                                                class="btn btn-sm bg-primary/10 text-primary"
                                                aria-haspopup="dialog"
                                                aria-label="Ver detalhes de {{ $registro['colaborador']->nome }}"
                                                title="Ver detalhamento"
                                                wire:loading.attr="disabled"
                                                wire:target="abrirDetalhes"
                                                @click="if (await $wire.abrirDetalhes({{ $prestadorId }})) $refs.detalhesProdutividade.showModal()"
                                            >
                                                <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path fill="currentColor" d="M3 3h18a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1m1 2v14h16V5zm3 8h2v4H7zm4-6h2v10h-2zm4 3h2v7h-2z"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3.5 py-8 text-center text-default-500">
                                            Nenhuma atividade PJ concluída encontrada para os filtros selecionados.
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

    @php($detalhes = $prestadorDetalhadoId === null ? null : $this->relatorio->get($prestadorDetalhadoId))
    <dialog
        x-ref="detalhesProdutividade"
        @click.self="$el.close()"
        aria-labelledby="detalhes-produtividade-titulo"
        class="fixed inset-0 m-auto max-h-[90dvh] w-11/12 max-w-none overflow-y-auto rounded-lg bg-white text-default-800 shadow-xl backdrop:bg-black/50 dark:bg-default-100"
    >
        <div class="flex items-center justify-between gap-3 border-b border-default-200 p-4">
            <h2 id="detalhes-produtividade-titulo" class="text-lg font-semibold">
                Detalhamento de produtividade{{ $detalhes ? ' — '.$detalhes['colaborador']->nome : '' }}
            </h2>
            <form method="dialog">
                <button type="submit" class="btn btn-sm bg-default-150 text-default-800" autofocus>Fechar</button>
            </form>
        </div>

        <div class="flex flex-col gap-4 p-4">
            @if ($detalhes)
                @foreach ($detalhes['projetos'] as $projetoId => $grupo)
                    <section wire:key="detalhe-pj-{{ $prestadorDetalhadoId }}-{{ $projetoId }}" class="flex flex-col gap-2">
                        <h3 class="font-semibold">{{ $grupo['projeto']->nome }}</h3>
                        <div class="overflow-x-auto rounded border border-default-200">
                            <table class="min-w-full divide-y divide-default-200 text-xs whitespace-nowrap">
                                <thead class="bg-default-150 text-default-700">
                                    <tr>
                                        <th class="px-3 py-2 text-start" scope="col">Atividade</th>
                                        <th class="px-3 py-2 text-start" scope="col">Conclusão</th>
                                        <th class="px-3 py-2 text-start" scope="col">Desenho (m)</th>
                                        <th class="px-3 py-2 text-start" scope="col">Projeto (m)</th>
                                        <th class="px-3 py-2 text-start" scope="col">Desenhados</th>
                                        <th class="px-3 py-2 text-start" scope="col">Projetados</th>
                                        <th class="px-3 py-2 text-start" scope="col">Desenho</th>
                                        <th class="px-3 py-2 text-start" scope="col">Projeto CAD</th>
                                        <th class="px-3 py-2 text-start" scope="col">Projeto PROJ</th>
                                        <th class="px-3 py-2 text-start" scope="col">Relatório</th>
                                        <th class="px-3 py-2 text-start" scope="col">Análise Proj</th>
                                        <th class="px-3 py-2 text-start" scope="col">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-default-200">
                                    @foreach ($grupo['atividades'] as $atividade)
                                        @php($valores = $atividade->remuneracao_pj)
                                        @php($total = $valores['desenho'] + $valores['projeto'] + $valores['proj'] + $valores['relatorio'] + $valores['analise_proj'])
                                        <tr wire:key="atividade-pj-{{ $atividade->id }}">
                                            <td class="min-w-40 max-w-60 whitespace-normal break-words px-3 py-2">{{ $atividade->nome }}</td>
                                            <td class="px-3 py-2">{{ $atividade->data_conclusao?->format('d/m/Y') ?? '—' }}</td>
                                            <td class="px-3 py-2">{{ (int) $atividade->extensao_desenho }}</td>
                                            <td class="px-3 py-2">{{ (int) $atividade->extensao_projeto }}</td>
                                            <td class="px-3 py-2">{{ (int) $atividade->postes_desenhados }}</td>
                                            <td class="px-3 py-2">{{ (int) $atividade->postes_projetados }}</td>
                                            <td class="px-3 py-2">{{ $this->formatarDinheiro($valores['desenho']) }}</td>
                                            <td class="px-3 py-2">{{ $this->formatarDinheiro($valores['projeto']) }}</td>
                                            <td class="px-3 py-2">{{ $this->formatarDinheiro($valores['proj']) }}</td>
                                            <td class="px-3 py-2">{{ $this->formatarDinheiro($valores['relatorio']) }}</td>
                                            <td class="px-3 py-2">{{ $this->formatarDinheiro($valores['analise_proj']) }}</td>
                                            <td class="px-3 py-2 font-semibold">{{ $this->formatarDinheiro($total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach
            @else
                <p class="text-sm text-default-500">Nenhuma atividade encontrada para os filtros selecionados.</p>
            @endif
        </div>
    </dialog>
</div>
