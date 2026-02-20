<?php

namespace App\Livewire\Admin;

use App\Actions\CreateOrUpdateColaborador;
use App\Enums\TipoContrato;
use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\Projeto;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use SweetAlert2\Laravel\Traits\WithSweetAlert;

class ColaboradoresList extends Component
{
    use WithPagination;
    use WithSweetAlert;

    #[Url(as: 'busca')]
    public string $search = '';

    #[Url(as: 'role')]
    public string $roleFilter = '';

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    #[On('colaborador-saved')]
    public function refresh(): void
    {
        // Livewire will automatically re-render the component
    }

    #[Computed]
    public function colaboradores()
    {
        return Colaborador::query()
            ->with('user')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nome', 'like', "%{$this->search}%")
                        ->orWhereHas('user', function ($userQuery) {
                            $userQuery->where('email', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->roleFilter, function ($query) {
                $role = UserRole::tryFrom($this->roleFilter);
                if ($role) {
                    $query->whereHas('user', fn ($q) => $q->role($role));
                }
            })
            ->orderBy('nome')
            ->paginate(10);
    }

    #[Computed]
    public function perfis(): array
    {
        return UserRole::perfisColaborador();
    }

    #[Computed]
    public function contratos(): array
    {
        return TipoContrato::options();
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', Projeto::class);
        $this->dispatch('open-colaborador-modal')->to(ColaboradorForm::class);
    }

    public function openEditModal(int $colaboradorId): void
    {
        $this->authorize('create', Projeto::class);
        $this->dispatch('open-colaborador-modal', colaboradorId: $colaboradorId)->to(ColaboradorForm::class);
    }

    public function confirmDelete(int $colaboradorId): void
    {
        $this->authorize('create', Projeto::class);

        $this->deletingId = $colaboradorId;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $this->authorize('create', Projeto::class);

        if (! $this->deletingId) {
            return;
        }

        $colaborador = Colaborador::findOrFail($this->deletingId);
        $colaborador->user->delete();
        $colaborador->delete();

        $this->swalToastWarning([
            'title' => 'Excluído com sucesso!',
            'showConfirmButton' => false,
            'position' => 'top-end',
            'timer' => 2000,
        ]);

        $this->closeDeleteModal();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function render(): View
    {
        return view('livewire.admin.colaboradores-list');
    }
}
