<div class="card-header">
    <div class="grid w-full grid-cols-9 gap-3 py-5">
        <div
            class="relative col-span-3 min-w-0"
            x-data="{
                open: false,
                search: '',
                selectedId: @entangle('projetoId').live,
                projetos: @js($this->projetos->map(fn ($projeto) => ['id' => $projeto->id, 'nome' => $projeto->nome])->values()),
                get selectedNome() {
                    if (!this.selectedId) {
                        return '';
                    }

                    const projetoSelecionado = this.projetos.find((projeto) => Number(projeto.id) === Number(this.selectedId));

                    return projetoSelecionado ? projetoSelecionado.nome : '';
                },
                get projetosFiltrados() {
                    if (!this.search) {
                        return this.projetos;
                    }

                    return this.projetos.filter((projeto) => projeto.nome.toLowerCase().includes(this.search.toLowerCase()));
                },
                selecionarProjeto(id) {
                    this.selectedId = Number(id);
                    this.open = false;
                    this.search = '';
                },
                limparProjeto() {
                    this.selectedId = null;
                    this.search = '';
                    this.open = false;
                }
            }"
            @click.outside="open = false"
        >
            <label class="mb-1 block text-sm font-medium text-default-700">Projeto</label>
            <button
                type="button"
                class="form-input form-input-sm inline-flex w-full max-w-full min-w-0 items-center overflow-hidden text-start"
                @click="open = !open"
            >
                <span class="block min-w-0 flex-1 overflow-hidden text-ellipsis whitespace-nowrap" x-text="selectedNome || 'Todos os projetos'"></span>
            </button>

            <div x-show="open" x-cloak class="absolute z-30 mt-1 w-full rounded-md border border-default-200 bg-white p-2 shadow-lg">
                <input
                    x-model="search"
                    type="text"
                    class="form-input form-input-sm mb-2 w-full"
                    placeholder="Digite para buscar..."
                />

                <div class="max-h-48 overflow-y-auto">
                    <button
                        type="button"
                        class="w-full rounded px-2 py-1.5 text-start text-sm hover:bg-default-100"
                        @click="limparProjeto()"
                    >
                        Todos os projetos
                    </button>
                    <template x-for="projeto in projetosFiltrados" :key="projeto.id">
                        <button
                            type="button"
                            class="w-full rounded px-2 py-1.5 text-start text-sm hover:bg-default-100"
                            @click="selecionarProjeto(projeto.id)"
                            x-text="projeto.nome"
                        ></button>
                    </template>
                </div>
            </div>
        </div>

        <div class="col-span-3">
            <label class="mb-1 block text-sm font-medium text-default-700">Coordenador</label>
            <select wire:model.live="coordenadorId" class="form-input form-input-sm w-full">
                <option value="">Todos os coordenadores</option>
                @foreach ($this->coordenadores as $coordenador)
                    <option value="{{ $coordenador->id }}">{{ $coordenador->nome }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-span-3">
            <label class="mb-1 block text-sm font-medium text-default-700">Mês/Ano</label>
            <select wire:model.live="mesAno" class="form-input form-input-sm w-full">
                @if ($mostrarAtalhoMesAtual ?? true)
                    <option value="">Mês atual até hoje</option>
                @endif
                @foreach ($this->competenciasDisponiveis as $valor => $rotulo)
                    <option value="{{ $valor }}">{{ $rotulo }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
