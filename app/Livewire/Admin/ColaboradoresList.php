<?php

namespace App\Livewire\Admin;

use App\Actions\CreateOrUpdateColaborador;
use App\Enums\TipoContrato;
use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
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

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $nome = '';

    public string $email = '';

    public string $role = '';

    public string $contrato = '';

    public ?int $userId = null;

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    protected function rules(): array
    {
        $rules = [
            'nome' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::enum(UserRole::class), Rule::notIn([UserRole::SuperAdmin->value])],
            'contrato' => ['required', Rule::enum(TipoContrato::class)],
        ];

        if ($this->editingId) {
            $rules['userId'] = ['required', 'exists:users,id', Rule::unique('colaboradores', 'user_id')->ignore($this->editingId)];
        } else {
            $rules['email'] = ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
            'role.required' => 'O perfil é obrigatório.',
            'role.enum' => 'O perfil selecionado é inválido.',
            'contrato.required' => 'O contrato é obrigatório.',
            'contrato.enum' => 'O contrato selecionado é inválido.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser um endereço válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'userId.required' => 'O usuário é obrigatório.',
            'userId.exists' => 'O usuário selecionado não existe.',
            'userId.unique' => 'Este usuário já possui um colaborador vinculado.',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
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
        $this->ensureUserIsAuthorized();
        $this->resetForm();
        $this->editingId = null;
        $this->email = '';
        $this->role = UserRole::Levantadores->value;
        $this->contrato = TipoContrato::CLT->value;
        $this->showModal = true;
    }

    public function openEditModal(int $colaboradorId): void
    {
        $this->ensureUserIsAuthorized();

        $colaborador = Colaborador::with('user')->findOrFail($colaboradorId);

        $this->editingId = $colaborador->id;
        $this->nome = $colaborador->nome;
        $this->email = $colaborador->user->email;
        $this->role = $colaborador->user->role->value;
        $this->contrato = $colaborador->contrato->value;
        $this->userId = $colaborador->user_id;
        $this->showModal = true;
    }

    public function save(CreateOrUpdateColaborador $action): void
    {
        $this->ensureUserIsAuthorized();
        $this->validate();

        $role = UserRole::from($this->role);
        $contrato = TipoContrato::from($this->contrato);

        if ($this->editingId) {
            $colaborador = Colaborador::findOrFail($this->editingId);
            $action->update(
                $colaborador,
                $this->nome,
                $role,
                $contrato,
                $this->userId
            );
        } else {
            $action->create(
                $this->nome,
                $this->email,
                $role,
                $contrato
            );
        }

        $this->swalToastSuccess([
            'title' => 'Salvo com sucesso!',
            'showConfirmButton' => false,
            'position' => 'top-end',
            'timer' => 2000,
        ]);

        $this->closeModal();
    }

    public function confirmDelete(int $colaboradorId): void
    {
        $this->ensureUserIsAuthorized();

        $this->deletingId = $colaboradorId;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $this->ensureUserIsAuthorized();

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

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    protected function resetForm(): void
    {
        $this->nome = '';
        $this->email = '';
        $this->role = '';
        $this->contrato = '';
        $this->userId = null;
        $this->editingId = null;
        $this->resetValidation();
    }

    protected function ensureUserIsAuthorized(): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user || (! $user->isAdminOrSuperAdmin() && ! $user->isCoordenador())) {
            abort(403, 'Você não tem permissão para acessar esta funcionalidade.');
        }
    }

    public function render(): View
    {
        return view('livewire.admin.colaboradores-list');
    }
}
