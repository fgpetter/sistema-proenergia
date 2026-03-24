<?php

namespace App\Livewire\Admin;

use App\Models\Projeto;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use SweetAlert2\Laravel\Traits\WithSweetAlert;

class ProjetosList extends Component
{
    use WithPagination;
    use WithSweetAlert;

    #[Url(as: 'busca')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[On('projeto-saved')]
    public function onProjetoSaved(): void
    {
        //
    }

    #[Computed]
    public function projetos()
    {
        $query = Projeto::query()
            ->with('responsavel', 'partes');

        if ($this->search) {
            $query->where('nome', 'like', "%{$this->search}%");
        }

        $user = auth()->user();
        if ($user && ! $user->can('create', Projeto::class)) {
            if ($user->colaborador) {
                $query->whereHas('partes', fn ($q) => $q->where('colaborador_id', $user->colaborador->id));
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query->orderBy('nome')
            ->paginate(10);
    }

    public function openCreateDrawer(): void
    {
        $this->dispatch('open-projeto-drawer')->to(ProjetoEditDrawer::class);
    }

    public function openEditDrawer(int $projetoId): void
    {
        $this->dispatch('open-projeto-drawer', projetoId: $projetoId)->to(ProjetoEditDrawer::class);
    }

    public ?int $deletingId = null;

    public function confirmDelete(int $projetoId): void
    {
        $projeto = Projeto::findOrFail($projetoId);
        $this->authorize('delete', $projeto);
        $this->deletingId = $projetoId;

        $componentId = $this->getId();
        $this->swalFire([
            'title' => 'Excluir projeto?',
            'text' => 'Tem certeza que deseja excluir este projeto e todas as suas partes? Esta ação não pode ser desfeita.',
            'icon' => 'warning',
            'showCancelButton' => true,
            'confirmButtonText' => 'Sim, excluir',
            'cancelButtonText' => 'Cancelar',
            'preConfirm' => "() => Livewire.find('{$componentId}').\$call('delete')",
        ]);
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $projeto = Projeto::findOrFail($this->deletingId);
        $this->authorize('delete', $projeto);
        $projeto->delete();
        $this->deletingId = null;

        $this->swalToastWarning([
            'title' => 'Excluído com sucesso!',
            'showConfirmButton' => false,
            'position' => 'top-end',
            'timer' => 2000,
        ]);
    }

    public function render(): View
    {
        return view('livewire.admin.projetos-list');
    }
}
