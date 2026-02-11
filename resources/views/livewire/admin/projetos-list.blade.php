<div
    x-data="{
        showDrawer: @entangle('showDrawer'),
        showDeleteModal: @entangle('showDeleteModal'),
        showRemoveParteModal: @entangle('showRemoveParteModal'),
        expandedParte: null
    }"
    x-init="
        $watch('showDrawer', value => {
            if (value) document.body.classList.add('overflow-hidden');
            else document.body.classList.remove('overflow-hidden');
        });
        $watch('showDeleteModal', value => {
            if (value) document.body.classList.add('overflow-hidden');
            else document.body.classList.remove('overflow-hidden');
        });
        $watch('showRemoveParteModal', value => {
            if (value) document.body.classList.add('overflow-hidden');
            else document.body.classList.remove('overflow-hidden');
        });
    "
>

    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Gestão de Projetos</h6>
            @can('admin-or-coordenador')
                <button @click="$wire.openCreateDrawer()" class="btn btn-sm bg-primary text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Novo Projeto
                </button>
            @endcan
        </div>
        <div class="card-header">
            <div class="md:flex items-center md:space-y-0 space-y-4 gap-3 w-full">
                <div class="relative flex-1">
                    <input
                        wire:model.live.debounce.300ms="search"
                        class="form-input form-input-sm ps-9"
                        placeholder="Buscar por nome"
                        type="text"
                    />
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-default-500"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col">
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-default-200">
                            <thead class="bg-default-150">
                                <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                                    <th class="px-3.5 py-3 text-start" scope="col">ID</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Nome</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Responsável</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Qtd Partes</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Criado em</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->projetos as $projeto)
                                    <tr wire:key="projeto-{{ $projeto->id }}" class="text-default-800 font-normal text-sm whitespace-nowrap">
                                        <td class="px-3.5 py-3 text-primary">#{{ $projeto->id }}</td>
                                        <td class="px-3.5 py-3">
                                            <h6 class="mb-0.5 font-semibold text-default-800">{{ $projeto->nome }}</h6>
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <span class="py-0.5 px-2.5 inline-flex items-center gap-x-1 text-xs font-medium bg-primary/10 text-primary rounded">
                                                {{ $projeto->responsavel->nome }}
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <span class="py-0.5 px-2.5 inline-flex items-center gap-x-1 text-xs font-medium bg-info/10 text-info rounded">
                                                {{ $projeto->partes->count() }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3.5">{{ $projeto->created_at->format('d/m/Y') }}</td>
                                        <td class="px-3.5 py-3">
                                            <div class="flex items-center gap-2">
                                                @can('view', $projeto)
                                                    <button
                                                        type="button"
                                                        @click="$wire.openEditDrawer({{ $projeto->id }})"
                                                        class="btn size-7.5 bg-default-200 hover:bg-primary/10 text-default-500 hover:text-primary"
                                                        title="Editar"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    </button>
                                                @endcan
                                                @can('delete', $projeto)
                                                    <button
                                                        type="button"
                                                        @click="$wire.confirmDelete({{ $projeto->id }})"
                                                        class="btn size-7.5 bg-default-200 hover:bg-danger/10 text-default-500 hover:text-danger"
                                                        title="Excluir"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3.5 py-8 text-center text-default-500">
                                            <div class="flex flex-col items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-default-300"><path d="M11 2H2v11a9 9 0 0 0 18 0V6a3 3 0 0 0-3-3h-5"/></svg>
                                                <p>Nenhum projeto encontrado.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @if ($this->projetos->hasPages())
                <div class="card-footer">
                    <p class="text-default-500 text-sm">
                        Exibindo <b>{{ $this->projetos->firstItem() ?? 0 }}</b> a <b>{{ $this->projetos->lastItem() ?? 0 }}</b> de <b>{{ $this->projetos->total() }}</b> resultados
                    </p>
                    <nav aria-label="Pagination" class="flex items-center gap-2">
                        @if ($this->projetos->onFirstPage())
                            <button disabled class="btn btn-sm border bg-transparent border-default-200 text-default-400 cursor-not-allowed" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="15 18 9 12 15 6"/></svg> Anterior
                            </button>
                        @else
                            <button wire:click="previousPage" class="btn btn-sm border bg-transparent border-default-200 text-default-600 hover:bg-primary/10 hover:text-primary hover:border-primary/10" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="15 18 9 12 15 6"/></svg> Anterior
                            </button>
                        @endif

                        @foreach ($this->projetos->getUrlRange(1, $this->projetos->lastPage()) as $page => $url)
                            @if ($page == $this->projetos->currentPage())
                                <button class="btn size-7.5 bg-primary text-white" type="button">{{ $page }}</button>
                            @else
                                <button wire:click="gotoPage({{ $page }})" class="btn size-7.5 bg-transparent border border-default-200 text-default-600 hover:bg-primary/10 hover:text-primary hover:border-primary/10" type="button">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach

                        @if ($this->projetos->hasMorePages())
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

                            <!-- Partes do Projeto -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-semibold text-default-800">Partes do Projeto</h4>
                                    @can('create', App\Models\Parte::class)
                                        <button
                                            type="button"
                                            @click="$wire.addParte()"
                                            class="btn btn-sm bg-success text-white"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            Adicionar
                                        </button>
                                    @endcan
                                </div>

                                <div class="space-y-4">
                                    @forelse ($partes as $index => $parte)
                                        @php
                                            $parteModel = isset($parte['id']) ? \App\Models\Parte::find($parte['id']) : null;
                                        @endphp
                                        @if (!($parte['_delete'] ?? false))
                                            <div class="border border-default-200 rounded-md overflow-hidden" wire:key="parte-{{ $index }}">
                                                <!-- Header do Card -->
                                                <button
                                                    type="button"
                                                    @click="expandedParte = expandedParte === {{ $index }} ? null : {{ $index }}"
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
                                                            :class="expandedParte === {{ $index }} ? 'rotate-180' : ''"
                                                        ><polyline points="6 9 12 15 18 9"/></svg>
                                                        <span class="font-medium text-default-800">
                                                            {{ $parte['nome'] ?: 'Parte sem nome' }}
                                                        </span>
                                                        @if ($parte['colaborador_id'])
                                                            <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded">
                                                                {{ $this->colaboradoresParaPartes[$parte['colaborador_id']] ?? 'Colaborador' }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </button>

                                                <!-- Conteúdo Expandível -->
                                                <div x-show="expandedParte === {{ $index }}" class="border-t border-default-200 p-3 space-y-3 bg-default-50">
                                                    <div>
                                                        <label class="block text-sm font-medium text-default-700 mb-1">Nome</label>
                                                        <input
                                                            wire:model="partes.{{ $index }}.nome"
                                                            type="text"
                                                            class="form-input w-full text-sm @error('partes.'.$index.'.nome') border-danger @enderror"
                                                            placeholder="Nome da parte"
                                                        >
                                                        @error('partes.'.$index.'.nome')
                                                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <div>
                                                        <label class="block text-sm font-medium text-default-700 mb-1">Colaborador</label>
                                                        <select
                                                            wire:model="partes.{{ $index }}.colaborador_id"
                                                            class="form-input w-full text-sm @error('partes.'.$index.'.colaborador_id') border-danger @enderror"
                                                            @if (!auth()->user()?->can('create', App\Models\Parte::class))
                                                                :disabled="$wire.partes[{{ $index }}]?.colaborador_id && $wire.partes[{{ $index }}]?.colaborador_id !== @js(auth()->user()->colaborador?->id)"
                                                            @endif
                                                        >
                                                            <option value="">Sem atribuição</option>
                                                            @foreach ($this->colaboradoresParaPartes as $colabId => $colabNome)
                                                                <option value="{{ $colabId }}">{{ $colabNome }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('partes.'.$index.'.colaborador_id')
                                                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <label class="block text-sm font-medium text-default-700 mb-1">Extensão Desenho (m)</label>
                                                            <input
                                                                wire:model.blur="partes.{{ $index }}.extensao_desenho"
                                                                type="number"
                                                                min="0"
                                                                class="form-input w-full text-sm @error('partes.'.$index.'.extensao_desenho') border-danger @enderror"
                                                            >
                                                            @error('partes.'.$index.'.extensao_desenho')
                                                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                            @enderror
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-default-700 mb-1">Extensão Projeto (m)</label>
                                                            <input
                                                                wire:model.blur="partes.{{ $index }}.extensao_projeto"
                                                                type="number"
                                                                min="0"
                                                                class="form-input w-full text-sm @error('partes.'.$index.'.extensao_projeto') border-danger @enderror"
                                                            >
                                                            @error('partes.'.$index.'.extensao_projeto')
                                                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <label class="block text-sm font-medium text-default-700 mb-1">Postes Desenhados</label>
                                                            <input
                                                                wire:model.blur="partes.{{ $index }}.postes_desenhados"
                                                                type="number"
                                                                min="0"
                                                                class="form-input w-full text-sm @error('partes.'.$index.'.postes_desenhados') border-danger @enderror"
                                                            >
                                                            @error('partes.'.$index.'.postes_desenhados')
                                                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                            @enderror
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-default-700 mb-1">Postes Projetados</label>
                                                            <input
                                                                wire:model.blur="partes.{{ $index }}.postes_projetados"
                                                                type="number"
                                                                min="0"
                                                                class="form-input w-full text-sm @error('partes.'.$index.'.postes_projetados') border-danger @enderror"
                                                            >
                                                            @error('partes.'.$index.'.postes_projetados')
                                                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <!-- Totais (Read-only) -->
                                                    <div class="bg-primary/5 border border-primary/20 rounded p-2 mt-2">
                                                        <div class="grid grid-cols-2 gap-2">
                                                            <div>
                                                                <p class="text-xs text-default-600">Total Extensão</p>
                                                                <p class="text-sm font-semibold text-primary">
                                                                    {{ ($parte['extensao_desenho'] ?? 0) + ($parte['extensao_projeto'] ?? 0) }} m
                                                                </p>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs text-default-600">Total Postes</p>
                                                                <p class="text-sm font-semibold text-primary">
                                                                    {{ ($parte['postes_desenhados'] ?? 0) + ($parte['postes_projetados'] ?? 0) }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Botões de Ação da Parte -->
                                                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-default-200 mt-3">
                                                        @if (!isset($parte['id']) || ($parteModel && auth()->user()?->can('delete', $parteModel)))
                                                            <button type="button"
                                                                wire:click="confirmRemoveParte({{ $index }})"
                                                                class="btn btn-sm bg-danger text-white hover:bg-danger/90"
                                                            >
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                                Remover
                                                            </button>
                                                        @endif
                                                        @if (!isset($parte['id']) ? auth()->user()?->can('create', App\Models\Parte::class) : ($parteModel && auth()->user()?->can('update', $parteModel)))
                                                            <button type="button"
                                                                wire:click="saveParte({{ $index }})"
                                                                class="btn btn-sm bg-primary text-white hover:bg-primary/90 inline-flex items-center gap-1"
                                                                wire:loading.attr="disabled"
                                                            >
                                                            <span wire:loading.remove wire:target="saveParte({{ $index }})" class="inline-flex items-center gap-1">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                                Salvar
                                                            </span>
                                                            <span wire:loading wire:target="saveParte({{ $index }})">Salvando...</span>
                                                        </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @empty
                                        <div class="text-center py-4 text-default-500 text-sm">
                                            Nenhuma parte adicionada. Use o botão acima para adicionar.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
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

    <!-- Modal Confirmar Exclusão -->
    <template x-teleport="body">
        <div
            x-show="showDeleteModal"
            x-cloak
            class="size-full fixed top-0 start-0 z-80 overflow-x-hidden overflow-y-auto pointer-events-none"
            role="dialog"
            tabindex="-1"
            aria-labelledby="delete-modal-title"
        >
            <!-- Backdrop -->
            <div
                x-show="showDeleteModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/50 pointer-events-auto"
                @click="$wire.closeDeleteModal()"
            ></div>

            <!-- Modal Content -->
            <div class="sm:max-w-lg sm:w-full m-3 sm:mx-auto min-h-[calc(100%-56px)] flex items-center relative z-10">
                <div
                    x-show="showDeleteModal"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="w-full flex flex-col bg-white border border-default-200 shadow-lg rounded-md pointer-events-auto"
                    @click.stop
                >
                    <div class="flex justify-between items-center p-4 border-b border-default-200">
                        <h3 id="delete-modal-title" class="font-bold text-default-800 text-base">
                            Excluir Projeto
                        </h3>
                        <button type="button" aria-label="Fechar" @click="$wire.closeDeleteModal()">
                            <span class="sr-only">Fechar</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <div class="p-4 overflow-y-auto">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-danger/10">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-danger"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            </div>
                            <div>
                                <p class="text-sm text-default-500">
                                    Tem certeza que deseja excluir este projeto e todas as suas partes? Esta ação não pode ser desfeita.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 p-4 border-t border-default-200">
                        <button
                            type="button"
                            @click="$wire.closeDeleteModal()"
                            class="btn bg-default-200 text-default-600 hover:bg-default-300"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            wire:click="delete"
                            class="btn bg-danger text-white hover:bg-danger/90"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="delete">Excluir</span>
                            <span wire:loading wire:target="delete">Excluindo...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal Confirmar Remoção de Parte -->
    <template x-teleport="body">
        <div
            x-show="showRemoveParteModal"
            x-cloak
            class="size-full fixed top-0 start-0 z-80 overflow-x-hidden overflow-y-auto pointer-events-none"
            role="dialog"
            tabindex="-1"
            aria-labelledby="remove-parte-modal-title"
        >
            <!-- Backdrop -->
            <div
                x-show="showRemoveParteModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/50 pointer-events-auto"
                @click="$wire.closeRemoveParteModal()"
            ></div>

            <!-- Modal Content -->
            <div class="sm:max-w-lg sm:w-full m-3 sm:mx-auto min-h-[calc(100%-56px)] flex items-center relative z-10">
                <div
                    x-show="showRemoveParteModal"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="w-full flex flex-col bg-white border border-default-200 shadow-lg rounded-md pointer-events-auto"
                    @click.stop
                >
                    <div class="flex justify-between items-center p-4 border-b border-default-200">
                        <h3 id="remove-parte-modal-title" class="font-bold text-default-800 text-base">
                            Remover Parte
                        </h3>
                        <button type="button" aria-label="Fechar" @click="$wire.closeRemoveParteModal()">
                            <span class="sr-only">Fechar</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <div class="p-4 overflow-y-auto">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-danger/10">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-danger"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            </div>
                            <div>
                                <p class="text-sm text-default-500">
                                    Tem certeza que deseja remover esta parte? Esta ação não pode ser desfeita.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 p-4 border-t border-default-200">
                        <button
                            type="button"
                            @click="$wire.closeRemoveParteModal()"
                            class="btn bg-default-200 text-default-600 hover:bg-default-300"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            wire:click="removeParteConfirmed"
                            class="btn bg-danger text-white hover:bg-danger/90"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="removeParteConfirmed">Remover</span>
                            <span wire:loading wire:target="removeParteConfirmed">Removendo...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
