<div>
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Relatório de Produtividade de Colaboradores</h6>
        </div>
        <div class="card-header">
            <div class="grid grid-cols-8 gap-3 py-5">
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

                <div class="col-span-1">
                    <label class="mb-1 block text-sm font-medium text-default-700">Data início</label>
                    <input type="date" wire:model.live="dataInicio" class="form-input form-input-sm w-full" />
                </div>

                <div class="col-span-1">
                    <label class="mb-1 block text-sm font-medium text-default-700">Data fim</label>
                    <input type="date" wire:model.live="dataFim" class="form-input form-input-sm w-full" />
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
                                    <th class="px-3.5 py-3 text-start" scope="col">Colaborador</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Projetos</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Extensão de desenho</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Extensão de projeto</th>
                                    <th class="px-3.5 py-3 text-start bg-blue-50" scope="col">Extensão total</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Postes desenhados</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Postes projetados</th>
                                    <th class="px-3.5 py-3 text-start bg-blue-50" scope="col">Postes total</th>
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
                                        <td class="px-3.5 py-3">{{ (int) $colaborador->total_extensao_desenho }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $colaborador->total_extensao_projeto }}</td>
                                        <td class="px-3.5 py-3 bg-blue-50">{{ (int) $colaborador->total_extensao_desenho + (int) $colaborador->total_extensao_projeto }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $colaborador->total_postes_desenhados }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $colaborador->total_postes_projetados }}</td>
                                        <td class="px-3.5 py-3 bg-blue-50">{{ (int) $colaborador->total_postes_desenhados + (int) $colaborador->total_postes_projetados }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $interval->totalHours }}h {{ $interval->minutes }}min</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3.5 py-8 text-center text-default-500">
                                            Nenhum colaborador com partes atribuídas foi encontrado.
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
</div>
