<div>
    <div class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-3">
            <div>
                <h6 class="card-title mb-1">Checklist de Análise</h6>
                <p class="text-sm text-default-500 mb-0">
                    Projeto #{{ $projeto->id }} — {{ $projeto->nome }}
                </p>
            </div>
            <a href="{{ route('admin.projetos') }}" class="btn btn-sm border border-default-200 bg-transparent text-default-600 hover:bg-primary/10 hover:text-primary hover:border-primary/10">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="15 18 9 12 15 6"/></svg>
                Voltar aos projetos
            </a>
        </div>

        <div class="card-header border-t border-default-200">
            <nav class="flex flex-wrap gap-2" role="tablist">
                <button
                    type="button"
                    wire:click="setAba('urbano')"
                    class="text-sm py-2 px-4 rounded-md font-medium transition-all duration-300 {{ $aba === 'urbano' ? 'bg-primary text-white' : 'bg-default-100 text-default-500 hover:text-primary' }}"
                    role="tab"
                    aria-selected="{{ $aba === 'urbano' ? 'true' : 'false' }}"
                >
                    Redes Urbanas
                </button>
                <button
                    type="button"
                    wire:click="setAba('rural')"
                    class="text-sm py-2 px-4 rounded-md font-medium transition-all duration-300 {{ $aba === 'rural' ? 'bg-success text-white' : 'bg-default-100 text-default-500 hover:text-success' }}"
                    role="tab"
                    aria-selected="{{ $aba === 'rural' ? 'true' : 'false' }}"
                >
                    Redes Rurais
                </button>
            </nav>
        </div>

        <div
            class="card-body"
            wire:key="checklist-{{ $aba }}"
            x-data="projetoChecklist({
                projetoId: {{ $projeto->id }},
                aba: '{{ $aba }}',
                itemNumeros: {{ json_encode(collect($this->items)->pluck('numero')->values()) }},
            })"
            x-ref="checklistRoot"
            x-init="init()"
        >
            @if ($aba === 'urbano')
                <div class="mb-6 space-y-2">
                    <h2 class="text-lg font-semibold text-default-800">CHECKLIST DE ANÁLISE DE PROJETOS — REDES URBANAS</h2>
                    <p class="text-sm text-default-600">
                        Conformidade com Normas Técnicas NT 001, NT 004, NT 005, NT 006, NT 007, NT 008 e NT 0018 — inclui itens gerais e itens específicos de redes urbanas.
                    </p>
                    <p class="text-sm text-default-600">
                        Instruções: para cada item, selecione a conformidade (Sim / Não / N.A.) e registre observações. A coluna &quot;Tipo&quot; indica se o item é Geral (aplicável a qualquer rede) ou específico desta categoria de rede. Itens &quot;Não&quot; devem ser detalhados e corrigidos antes da aprovação.
                    </p>
                </div>
            @else
                <div class="mb-6 space-y-2">
                    <h2 class="text-lg font-semibold text-default-800">CHECKLIST DE ANÁLISE DE PROJETOS — REDES RURAIS</h2>
                    <p class="text-sm text-default-600">
                        Conformidade com Normas Técnicas NT 001, NT 004, NT 005, NT 006, NT 007, NT 008 e NT 0018 — inclui itens gerais e itens específicos de redes rurais.
                    </p>
                    <p class="text-sm text-default-600">
                        Instruções: para cada item, selecione a conformidade (Sim / Não / N.A.) e registre observações. A coluna &quot;Tipo&quot; indica se o item é Geral (aplicável a qualquer rede) ou específico desta categoria de rede. Itens &quot;Não&quot; devem ser detalhados e corrigidos antes da aprovação.
                    </p>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-default-200">
                    <thead class="bg-default-150">
                        <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                            <th class="px-3.5 py-3 text-start" scope="col">Nº</th>
                            <th class="px-3.5 py-3 text-start" scope="col">Categoria</th>
                            <th class="px-3.5 py-3 text-start min-w-64" scope="col">Item a Verificar</th>
                            <th class="px-3.5 py-3 text-start" scope="col">Norma(s)</th>
                            <th class="px-3.5 py-3 text-start" scope="col">Tipo</th>
                            <th class="px-3.5 py-3 text-center" scope="col">Sim</th>
                            <th class="px-3.5 py-3 text-center" scope="col">Não</th>
                            <th class="px-3.5 py-3 text-center" scope="col">N.A.</th>
                            <th class="px-3.5 py-3 text-start min-w-48" scope="col">Observações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-default-200">
                        @foreach ($this->items as $item)
                            <tr wire:key="item-{{ $aba }}-{{ $item['numero'] }}" class="text-default-800 font-normal text-sm">
                                <td class="px-3.5 py-3 whitespace-nowrap">{{ $item['numero'] }}</td>
                                <td class="px-3.5 py-3 min-w-48">{{ $item['categoria'] }}</td>
                                <td class="px-3.5 py-3 min-w-64">{{ $item['item'] }}</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">{{ $item['normas'] }}</td>
                                <td class="px-3.5 py-3 whitespace-nowrap">{{ $item['tipo'] }}</td>
                                <td class="px-3.5 py-3 text-center">
                                    <input
                                        type="radio"
                                        class="form-radio size-4 {{ $aba === 'rural' ? 'text-success' : 'text-primary' }}"
                                        name="conformidade-{{ $aba }}-{{ $item['numero'] }}"
                                        value="Sim"
                                        data-numero="{{ $item['numero'] }}"
                                        data-tipo="conformidade"
                                        @change="setConformidade({{ $item['numero'] }}, 'Sim')"
                                    />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input
                                        type="radio"
                                        class="form-radio size-4 {{ $aba === 'rural' ? 'text-success' : 'text-primary' }}"
                                        name="conformidade-{{ $aba }}-{{ $item['numero'] }}"
                                        value="Não"
                                        data-numero="{{ $item['numero'] }}"
                                        data-tipo="conformidade"
                                        @change="setConformidade({{ $item['numero'] }}, 'Não')"
                                    />
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <input
                                        type="radio"
                                        class="form-radio size-4 {{ $aba === 'rural' ? 'text-success' : 'text-primary' }}"
                                        name="conformidade-{{ $aba }}-{{ $item['numero'] }}"
                                        value="N.A."
                                        checked
                                        data-numero="{{ $item['numero'] }}"
                                        data-tipo="conformidade"
                                        @change="setConformidade({{ $item['numero'] }}, 'N.A.')"
                                    />
                                </td>
                                <td class="px-3.5 py-3">
                                    <input
                                        type="text"
                                        class="form-input form-input-sm w-full min-w-48"
                                        placeholder="Observações"
                                        data-numero="{{ $item['numero'] }}"
                                        data-tipo="observacao"
                                        @input.debounce.300ms="setObservacao({{ $item['numero'] }}, $event.target.value)"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end" wire:ignore>
                <button
                    type="button"
                    class="btn btn-sm border border-danger/30 bg-transparent text-danger hover:bg-danger/10"
                    x-on:click.prevent.stop="confirmReset()"
                >
                    Reset
                </button>
            </div>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('projetoChecklist', (config) => ({
        projetoId: config.projetoId,
        aba: config.aba,
        itemNumeros: config.itemNumeros,

        conformidadeKey(numero) {
            return `${this.projetoId}_${numero}_${this.aba}`;
        },

        observacaoKey(numero) {
            return `${this.projetoId}_${numero}_${this.aba}_comments`;
        },

        init() {
            this.itemNumeros.forEach((numero) => {
                const conformidade = localStorage.getItem(this.conformidadeKey(numero)) ?? 'N.A.';
                const observacao = localStorage.getItem(this.observacaoKey(numero)) ?? '';

                const radios = this.$el.querySelectorAll(`input[data-numero="${numero}"][data-tipo="conformidade"]`);
                radios.forEach((radio) => {
                    radio.checked = radio.value === conformidade;
                });

                const observacaoInput = this.$el.querySelector(`input[data-numero="${numero}"][data-tipo="observacao"]`);
                if (observacaoInput) {
                    observacaoInput.value = observacao;
                }
            });
        },

        setConformidade(numero, valor) {
            localStorage.setItem(this.conformidadeKey(numero), valor);
        },

        setObservacao(numero, valor) {
            const key = this.observacaoKey(numero);
            if (valor.trim() === '') {
                localStorage.removeItem(key);
            } else {
                localStorage.setItem(key, valor);
            }
        },

        async confirmReset() {
            const abaLabel = this.aba === 'urbano' ? 'Redes Urbanas' : 'Redes Rurais';
            const message = `Isso limpará todas as respostas da aba ${abaLabel} deste projeto.`;

            let confirmed = false;

            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: 'Resetar checklist?',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, resetar',
                    cancelButtonText: 'Cancelar',
                });

                confirmed = result.isConfirmed;
            } else {
                confirmed = window.confirm(`Resetar checklist? ${message}`);
            }

            if (confirmed) {
                this.resetAba();
            }
        },

        resetAba() {
            this.itemNumeros.forEach((numero) => {
                localStorage.removeItem(this.conformidadeKey(numero));
                localStorage.removeItem(this.observacaoKey(numero));
            });

            window.location.reload();
        },
    }));
</script>
@endscript
