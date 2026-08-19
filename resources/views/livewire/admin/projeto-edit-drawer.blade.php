<div
    x-data="{
        showDrawer: @entangle('showDrawer'),
        expandedAtividade: null
    }"
    x-init="
        $watch('showDrawer', value => {
            if (value) document.body.classList.add('overflow-hidden');
            else document.body.classList.remove('overflow-hidden');
        });
    "
>
    <!-- Drawer Criar/Editar Projeto -->
    <template x-teleport="body">
        <div
            x-show="showDrawer"
            x-cloak
            class="size-full fixed top-0 start-0 z-80 overflow-x-hidden overflow-y-auto pointer-events-none"
            role="dialog"
            tabindex="-1"
            aria-labelledby="drawer-title"
        >
            <!-- Backdrop -->
            <div
                x-show="showDrawer"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/50 pointer-events-auto"
                @click="$wire.closeDrawer()"
            ></div>

            <!-- Drawer Content -->
            <div class="fixed inset-y-0 end-0 w-full md:w-1/2 flex items-stretch pointer-events-none">
                <div
                    x-show="showDrawer"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="ease-in duration-300"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                    class="w-full flex flex-col bg-white border border-default-200 shadow-lg pointer-events-auto"
                    @click.stop
                >
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 border-b border-default-200">
                        <h3 id="drawer-title" class="font-bold text-default-800 text-base">
                            {{ $editingProjetoId ? 'Editar Projeto' : 'Novo Projeto' }}
                        </h3>
                        <button type="button" aria-label="Fechar" @click="$wire.closeDrawer()">
                            <span class="sr-only">Fechar</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <!-- Body com Scroll -->
                    <div class="flex-1 overflow-y-auto p-4">
                        <!-- Dados do Projeto -->
                        <div class="space-y-4">
                            <div>
                                <label for="nome" class="block text-sm font-medium text-default-700 mb-1">Nome do Projeto</label>
                                <input
                                    wire:model="nome"
                                    type="text"
                                    id="nome"
                                    class="form-input w-full @error('nome') border-danger @enderror"
                                    placeholder="Nome do projeto"
                                >
                                @error('nome')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="responsavel" class="block text-sm font-medium text-default-700 mb-1">Responsável</label>
                                <select
                                    wire:model="colaboradorResponsavelId"
                                    id="responsavel"
                                    class="form-input w-full @error('colaboradorResponsavelId') border-danger @enderror"
                                >
                                    <option value="">Selecione um responsável</option>
                                    @foreach ($this->coordenadoresDisponiveis as $id => $nome)
                                        <option value="{{ $id }}">{{ $nome }}</option>
                                    @endforeach
                                </select>
                                @error('colaboradorResponsavelId')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Atividades do Projeto -->
                            <div id="atividades-projeto">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-semibold text-default-800">Atividades do Projeto</h4>
                                    @can('create', App\Models\Atividade::class)
                                        <button
                                            type="button"
                                            @click="$wire.addAtividade()"
                                            class="btn btn-sm bg-success text-white"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            Adicionar
                                        </button>
                                    @endcan
                                </div>

                                <div class="space-y-4">
                                    @forelse ($atividades as $index => $atividade)
                                        @php
                                            $atividadeModel = isset($atividade['id']) ? \App\Models\Atividade::find($atividade['id']) : null;
                                        @endphp
                                        @if (!($atividade['_delete'] ?? false))
                                            <div class="border border-default-200 rounded-md overflow-hidden" wire:key="atividade-{{ $index }}">
                                                <!-- Header do Card -->
                                                <button
                                                    type="button"
                                                    @click="expandedAtividade = expandedAtividade === {{ $index }} ? null : {{ $index }}"
                                                    class="w-full flex items-center justify-between p-3 hover:bg-default-50 transition"
                                                >
                                                    <div class="flex items-center gap-2 flex-1 text-left">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="flex-shrink-0 transition-transform"
                                                            :class="expandedAtividade === {{ $index }} ? 'rotate-180' : ''"
                                                        ><polyline points="6 9 12 15 18 9"/></svg>
                                                        <span class="font-medium text-default-800">
                                                            {{ $atividade['nome'] ?: 'Atividade sem nome' }}
                                                        </span>
                                                        @if ($atividade['colaborador_id'])
                                                            <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded">
                                                                {{ $this->colaboradoresParaAtividades[$atividade['colaborador_id']] ?? 'Colaborador' }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </button>

                                                <!-- Conteúdo Expandível -->
                                                <div x-show="expandedAtividade === {{ $index }}" class="border-t border-default-200 p-3 space-y-3 bg-default-50">
                                                    <div>
                                                        <label class="block text-sm font-medium text-default-700 mb-1">Nome</label>
                                                        <input
                                                            wire:model="atividades.{{ $index }}.nome"
                                                            type="text"
                                                            class="form-input w-full text-sm @error('atividades.'.$index.'.nome') border-danger @enderror"
                                                            placeholder="Nome da atividade"
                                                        >
                                                        @error('atividades.'.$index.'.nome')
                                                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <div>
                                                        <label class="block text-sm font-medium text-default-700 mb-1">Colaborador</label>
                                                        <select
                                                            wire:model="atividades.{{ $index }}.colaborador_id"
                                                            class="form-input w-full text-sm @error('atividades.'.$index.'.colaborador_id') border-danger @enderror"
                                                            @if (!auth()->user()?->can('create', App\Models\Atividade::class))
                                                                :disabled="$wire.atividades[{{ $index }}]?.colaborador_id && $wire.atividades[{{ $index }}]?.colaborador_id !== @js(auth()->user()->colaborador?->id)"
                                                            @endif
                                                        >
                                                            <option value="">Sem atribuição</option>
                                                            @foreach ($this->colaboradoresParaAtividades as $colabId => $colabNome)
                                                                <option value="{{ $colabId }}">{{ $colabNome }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('atividades.'.$index.'.colaborador_id')
                                                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <label class="block text-sm font-medium text-default-700 mb-1">Extensão Desenho (m)</label>
                                                            <input
                                                                wire:model.live="atividades.{{ $index }}.extensao_desenho"
                                                                type="number"
                                                                min="0"
                                                                class="form-input w-full text-sm @error('atividades.'.$index.'.extensao_desenho') border-danger @enderror"
                                                            >
                                                            @error('atividades.'.$index.'.extensao_desenho')
                                                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                            @enderror
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-default-700 mb-1">Extensão Projeto (m)</label>
                                                            <input
                                                                wire:model.live="atividades.{{ $index }}.extensao_projeto"
                                                                type="number"
                                                                min="0"
                                                                class="form-input w-full text-sm @error('atividades.'.$index.'.extensao_projeto') border-danger @enderror"
                                                            >
                                                            @error('atividades.'.$index.'.extensao_projeto')
                                                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <label class="block text-sm font-medium text-default-700 mb-1">Postes Desenhados</label>
                                                            <input
                                                                wire:model.live="atividades.{{ $index }}.postes_desenhados"
                                                                type="number"
                                                                min="0"
                                                                class="form-input w-full text-sm @error('atividades.'.$index.'.postes_desenhados') border-danger @enderror"
                                                            >
                                                            @error('atividades.'.$index.'.postes_desenhados')
                                                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                            @enderror
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-default-700 mb-1">Postes Projetados</label>
                                                            <input
                                                                wire:model.live="atividades.{{ $index }}.postes_projetados"
                                                                type="number"
                                                                min="0"
                                                                class="form-input w-full text-sm @error('atividades.'.$index.'.postes_projetados') border-danger @enderror"
                                                            >
                                                            @error('atividades.'.$index.'.postes_projetados')
                                                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <label class="block text-sm font-medium text-default-700 mb-1">Tipo de Projeto</label>
                                                        <select
                                                            wire:model.live="atividades.{{ $index }}.tipo_projeto"
                                                            class="form-input w-full text-sm @error('atividades.'.$index.'.tipo_projeto') border-danger @enderror"
                                                        >
                                                            @foreach ($this->tiposProjetoDisponiveis as $tipoValue => $tipoLabel)
                                                                <option value="{{ $tipoValue }}">{{ $tipoLabel }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('atividades.'.$index.'.tipo_projeto')
                                                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <div class="w-1/2">
                                                        <div class="grid grid-cols-2 gap-2">
                                                            <div>
                                                                <label class="block text-sm font-medium text-default-700 mb-1">H</label>
                                                                <input
                                                                    wire:model="atividades.{{ $index }}.duracao_horas"
                                                                    type="number"
                                                                    min="0"
                                                                    class="form-input w-full text-sm @error('atividades.'.$index.'.duracao_horas') border-danger @enderror"
                                                                >
                                                                @error('atividades.'.$index.'.duracao_horas')
                                                                    <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                                @enderror
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-default-700 mb-1">M</label>
                                                                <input
                                                                    wire:model="atividades.{{ $index }}.duracao_minutos"
                                                                    type="number"
                                                                    min="0"
                                                                    max="59"
                                                                    class="form-input w-full text-sm @error('atividades.'.$index.'.duracao_minutos') border-danger @enderror"
                                                                >
                                                                @error('atividades.'.$index.'.duracao_minutos')
                                                                    <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <label class="block text-sm font-medium text-default-700 mb-1">Observações</label>
                                                        <textarea
                                                            wire:model="atividades.{{ $index }}.observacoes"
                                                            rows="3"
                                                            class="form-input w-full text-sm @error('atividades.'.$index.'.observacoes') border-danger @enderror"
                                                            placeholder="Observações sobre esta atividade..."
                                                        ></textarea>
                                                        @error('atividades.'.$index.'.observacoes')
                                                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <!-- Totais (Read-only) -->
                                                    <div class="bg-primary/5 border border-primary/20 rounded p-2 mt-2">
                                                        <div class="grid grid-cols-2 gap-2">
                                                            <div>
                                                                <p class="text-xs text-default-600">Total Extensão</p>
                                                                <p class="text-sm font-semibold text-primary">
                                                                    {{ (int) ($atividade['extensao_desenho'] ?? 0) + (int) ($atividade['extensao_projeto'] ?? 0) }} m
                                                                </p>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs text-default-600">Total Postes</p>
                                                                <p class="text-sm font-semibold text-primary">
                                                                    {{ (int) ($atividade['postes_desenhados'] ?? 0) + (int) ($atividade['postes_projetados'] ?? 0) }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Botões de Ação da Atividade -->
                                                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-default-200 mt-3">
                                                        @if (!isset($atividade['id']) || ($atividadeModel && auth()->user()?->can('delete', $atividadeModel)))
                                                            <button type="button"
                                                                wire:click="confirmRemoveAtividade({{ $index }})"
                                                                class="btn btn-sm bg-danger text-white hover:bg-danger/90"
                                                            >
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                                Remover
                                                            </button>
                                                        @endif
                                                        @if (!isset($atividade['id']) ? auth()->user()?->can('create', App\Models\Atividade::class) : ($atividadeModel && auth()->user()?->can('update', $atividadeModel)))
                                                            <button type="button"
                                                                wire:click="saveAtividade({{ $index }})"
                                                                class="btn btn-sm bg-primary text-white hover:bg-primary/90 inline-flex items-center gap-1"
                                                                wire:loading.attr="disabled"
                                                            >
                                                            <span wire:loading.remove wire:target="saveAtividade({{ $index }})" class="inline-flex items-center gap-1">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                                Salvar
                                                            </span>
                                                            <span wire:loading wire:target="saveAtividade({{ $index }})">Salvando...</span>
                                                        </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @empty
                                        <div class="text-center py-4 text-default-500 text-sm">
                                            Nenhuma atividade adicionada. Use o botão acima para adicionar.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Timeline de Atividades -->
                            @if ($editingProjetoId && $this->logAtividades->isNotEmpty())
                                <div>
                                    <h4 class="font-semibold text-default-800 mb-4">Histórico de Atividades</h4>
                                    <div>
                                        @foreach ($this->logAtividades as $atividade)
                                            <div wire:key="atividade-{{ $atividade->id }}" class="relative px-6 before:absolute before:border-s before:start-0.75 before:border-default-200 before:end-0.75 before:top-1.5 before:-bottom-1.5 after:absolute after:size-2 after:bg-primary after:rounded-full after:start-0 after:end-0 after:top-1.5 {{ $loop->last ? '' : 'pb-4' }}">
                                                <p class="text-sm text-default-800">
                                                    {{ $this->nomeUsuarioAtividade($atividade->user, $atividade->user_id) }}
                                                    {{ $atividade->acao }}
                                                    {{ $this->labelItemAtividade($atividade->item) }}
                                                    {{ $atividade->valor }}
                                                    em
                                                    {{ $this->nomeAtividadeLog($atividade->atividade, $atividade->atividade_id) }}
                                                </p>
                                                <p class="text-xs text-default-400 mt-1">
                                                    {{ $atividade->created_at->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if ($this->logAtividades->count() === $atividadesLimite)
                                        <div class="mt-3 text-center">
                                            <button
                                                type="button"
                                                wire:click="carregarMaisAtividades"
                                                class="btn btn-sm bg-default-100 text-default-600 hover:bg-default-200"
                                                wire:loading.attr="disabled"
                                            >
                                                <span wire:loading.remove wire:target="carregarMaisAtividades">Carregar mais</span>
                                                <span wire:loading wire:target="carregarMaisAtividades">Carregando...</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-2 p-4 border-t border-default-200">
                        <button
                            type="button"
                            @click="$wire.closeDrawer()"
                            class="btn bg-default-200 text-default-600 hover:bg-default-300"
                        >
                            Cancelar
                        </button>
                        @if ($editingProjetoId && $this->editingProjeto)
                            @can('update', $this->editingProjeto)
                                <button
                                    type="button"
                                    wire:click="save"
                                    class="btn bg-primary text-white hover:bg-primary/90"
                                    wire:loading.attr="disabled"
                                >
                                    <span wire:loading.remove wire:target="save">Salvar Alterações</span>
                                    <span wire:loading wire:target="save">Salvando...</span>
                                </button>
                            @endcan
                        @else
                            @can('create', App\Models\Projeto::class)
                                <button
                                    type="button"
                                    wire:click="save"
                                    class="btn bg-primary text-white hover:bg-primary/90"
                                    wire:loading.attr="disabled"
                                >
                                    <span wire:loading.remove wire:target="save">Criar Projeto</span>
                                    <span wire:loading wire:target="save">Salvando...</span>
                                </button>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
