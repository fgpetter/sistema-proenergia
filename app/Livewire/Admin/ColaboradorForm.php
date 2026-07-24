<?php

namespace App\Livewire\Admin;

use App\Actions\CreateOrUpdateColaborador;
use App\Enums\TipoContrato;
use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\User;
use App\Support\Money;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use SweetAlert2\Laravel\Traits\WithSweetAlert;

class ColaboradorForm extends Component
{
    use WithSweetAlert;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $nome = '';

    public string $email = '';

    public string $role = '';

    public string $contrato = '';

    public string $remuneracao = '';

    public ?int $userId = null;

    protected function rules(): array
    {
        $rules = [
            'nome' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::enum(UserRole::class), Rule::notIn([UserRole::SuperAdmin->value])],
            'contrato' => ['required', Rule::enum(TipoContrato::class)],
            'remuneracao' => ['nullable', 'string', 'max:32'],
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

    #[On('open-colaborador-modal')]
    public function openModal(?int $colaboradorId = null): void
    {
        $this->ensureUserIsAuthorized();
        $this->resetForm();

        if ($colaboradorId) {
            $colaborador = Colaborador::with('user')->findOrFail($colaboradorId);
            $this->editingId = $colaborador->id;
            $this->nome = $colaborador->nome;
            $this->email = $colaborador->user->email;
            $this->role = $colaborador->user->role->value;
            $this->contrato = $colaborador->contrato->value;
            $this->remuneracao = Money::fromCents($colaborador->remuneracao);
            $this->userId = $colaborador->user_id;
        } else {
            $this->editingId = null;
            $this->email = '';
            $this->role = UserRole::Levantadores->value;
            $this->contrato = TipoContrato::CLT->value;
            $this->remuneracao = '';
        }

        $this->showModal = true;
    }

    public function save(CreateOrUpdateColaborador $action): void
    {
        $this->ensureUserIsAuthorized();
        $this->validate();

        $role = UserRole::from($this->role);
        $contrato = TipoContrato::from($this->contrato);
        $remuneracao = Money::toCents($this->remuneracao);

        if ($this->editingId) {
            $colaborador = Colaborador::findOrFail($this->editingId);
            $action->update(
                $colaborador,
                $this->nome,
                $role,
                $contrato,
                $this->userId,
                $remuneracao,
            );
        } else {
            $action->create(
                $this->nome,
                $this->email,
                $role,
                $contrato,
                $remuneracao,
            );
        }

        $this->dispatch('colaborador-saved');

        $this->swalToastSuccess([
            'title' => 'Salvo com sucesso!',
            'showConfirmButton' => false,
            'position' => 'top-end',
            'timer' => 2000,
        ]);

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->nome = '';
        $this->email = '';
        $this->role = '';
        $this->contrato = '';
        $this->remuneracao = '';
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
        return view('livewire.admin.colaborador-form');
    }
}
