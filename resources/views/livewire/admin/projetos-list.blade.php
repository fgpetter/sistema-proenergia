<div>
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
                                            <span class="group relative cursor-pointer">
                                                <h6 class="mb-0.5 font-semibold text-default-800">
                                                    {{ Str::limit($projeto->nome, 100) }}
                                                </h6>
                                                <span class="opacity-0 group-hover:opacity-100 pointer-events-none absolute z-10 left-0 top-0 -translate-y-full mt-0 mb-2 px-2 py-1 text-xs leading-tight text-white bg-black rounded shadow-lg transition-opacity duration-200"
                                                      style="white-space: pre-line; min-width: 140px; max-width: 500px;">{{ $projeto->nome }}</span>
                                                 
                                            </span>
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
                                                        wire:click="confirmDelete({{ $projeto->id }})"
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

    <livewire:admin.projeto-edit-drawer />
</div>
