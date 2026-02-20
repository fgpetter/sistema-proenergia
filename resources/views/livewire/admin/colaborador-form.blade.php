<div
    x-data="{ showModal: @entangle('showModal') }"
    x-init="
        $watch('showModal', value => {
            if (value) document.body.classList.add('overflow-hidden');
            else document.body.classList.remove('overflow-hidden');
        });
    "
>
    <template x-teleport="body">
        <div
            x-show="showModal"
            x-cloak
            class="size-full fixed top-0 start-0 z-80 overflow-x-hidden overflow-y-auto pointer-events-none"
            role="dialog"
            tabindex="-1"
            aria-labelledby="modal-title"
        >
            <!-- Backdrop -->
            <div
                x-show="showModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/50 pointer-events-auto"
                @click="$wire.closeModal()"
            ></div>

            <!-- Modal Content -->
            <div class="sm:max-w-lg sm:w-full m-3 sm:mx-auto min-h-[calc(100%-56px)] flex items-center relative z-10">
                <div
                    x-show="showModal"
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
                        <h3 id="modal-title" class="font-bold text-default-800 text-base">
                            {{ $editingId ? 'Editar Colaborador' : 'Novo Colaborador' }}
                        </h3>
                        <button type="button" aria-label="Fechar" @click="$wire.closeModal()">
                            <span class="sr-only">Fechar</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="p-4 overflow-y-auto">
                            <div class="space-y-4">
                                <div>
                                    <label for="nome" class="block text-sm font-medium text-default-700 mb-1">Nome</label>
                                    <input
                                        wire:model="nome"
                                        type="text"
                                        id="nome"
                                        class="form-input w-full @error('nome') border-danger @enderror"
                                        placeholder="Nome completo"
                                    >
                                    @error('nome')
                                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="role" class="block text-sm font-medium text-default-700 mb-1">Perfil</label>
                                    <select
                                        wire:model="role"
                                        id="role"
                                        class="form-input w-full @error('role') border-danger @enderror"
                                    >
                                        <option value="">Selecione um perfil</option>
                                        @foreach ($this->perfis as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="contrato" class="block text-sm font-medium text-default-700 mb-1">Contrato</label>
                                    <select
                                        wire:model="contrato"
                                        id="contrato"
                                        class="form-input w-full @error('contrato') border-danger @enderror"
                                    >
                                        <option value="">Selecione um contrato</option>
                                        @foreach ($this->contratos as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('contrato')
                                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                @if ($editingId)
                                    <div>
                                        <label for="userName" class="block text-sm font-medium text-default-700 mb-1">Usuário</label>
                                        <input
                                            type="text"
                                            id="userName"
                                            class="form-input w-full bg-default-100"
                                            value="{{ $nome }}"
                                            disabled
                                        >
                                        <label for="userEmail" class="block text-sm font-medium text-default-700 mt-3">Email</label>
                                        <input
                                            type="text"
                                            id="userEmail"
                                            class="form-input w-full bg-default-100"
                                            value="{{ $email }}"
                                            disabled
                                        >
                                    </div>
                                @else
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-default-700 mb-1">E-mail</label>
                                        <input
                                            wire:model="email"
                                            type="email"
                                            id="email"
                                            class="form-input w-full @error('email') border-danger @enderror"
                                            placeholder="email@exemplo.com"
                                        >
                                        @error('email')
                                            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 p-4 border-t border-default-200">
                            <button
                                type="button"
                                @click="$wire.closeModal()"
                                class="btn bg-default-200 text-default-600 hover:bg-default-300"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="btn bg-primary text-white hover:bg-primary/90"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="save">
                                    {{ $editingId ? 'Salvar Alterações' : 'Criar Colaborador' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    Salvando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
