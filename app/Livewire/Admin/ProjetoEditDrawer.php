<?php

namespace App\Livewire\Admin;

use App\Actions\CreateOrUpdateAtividade;
use App\Actions\CreateOrUpdateProjeto;
use App\Enums\TipoProjetoAtividade;
use App\Enums\UserRole;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\LogAtividade;
use App\Models\Projeto;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use SweetAlert2\Laravel\Traits\WithSweetAlert;

class ProjetoEditDrawer extends Component
{
    use WithSweetAlert;

    public bool $showDrawer = false;

    public ?int $editingProjetoId = null;

    public int $atividadesLimite = 10;

    public string $nome = '';

    public ?int $colaboradorResponsavelId = null;

    public array $atividades = [];

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'colaboradorResponsavelId' => [
                'required',
                'exists:colaboradores,id',
                $this->coordenadorValidationRule(),
            ],
            'atividades.*.nome' => ['required', 'string', 'max:255'],
            'atividades.*.colaborador_id' => [
                'nullable',
                'exists:colaboradores,id',
                $this->atividadeColaboradorValidationRule(),
            ],
            'atividades.*.extensao_desenho' => ['required', 'integer', 'min:0'],
            'atividades.*.extensao_projeto' => ['required', 'integer', 'min:0'],
            'atividades.*.postes_desenhados' => ['required', 'integer', 'min:0'],
            'atividades.*.postes_projetados' => ['required', 'integer', 'min:0'],
            'atividades.*.tipo_projeto' => ['required', Rule::enum(TipoProjetoAtividade::class)],
            'atividades.*.duracao_horas' => ['nullable', 'integer', 'min:0'],
            'atividades.*.duracao_minutos' => ['nullable', 'integer', 'min:0', 'max:59'],
            'atividades.*.observacoes' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nome.required' => 'O nome do projeto é obrigatório.',
            'colaboradorResponsavelId.required' => 'O responsável é obrigatório.',
            'atividades.*.nome.required' => 'O nome da atividade é obrigatório.',
            'atividades.*.extensao_desenho.required' => 'A extensão de desenho é obrigatória.',
            'atividades.*.extensao_projeto.required' => 'A extensão de projeto é obrigatória.',
            'atividades.*.postes_desenhados.required' => 'O número de postes desenhados é obrigatório.',
            'atividades.*.postes_projetados.required' => 'O número de postes projetados é obrigatório.',
            'atividades.*.tipo_projeto.required' => 'O tipo de projeto é obrigatório.',
            'atividades.*.duracao_horas.integer' => 'As horas devem ser um número inteiro.',
            'atividades.*.duracao_horas.min' => 'As horas não podem ser negativas.',
            'atividades.*.duracao_minutos.integer' => 'Os minutos devem ser um número inteiro.',
            'atividades.*.duracao_minutos.min' => 'Os minutos não podem ser negativos.',
            'atividades.*.duracao_minutos.max' => 'Os minutos devem estar entre 0 e 59.',
        ];
    }

    protected function coordenadorValidationRule(): Closure
    {
        return function (string $attribute, mixed $value, callable $fail): void {
            $colaborador = Colaborador::with('user')->find($value);
            if ($colaborador && $colaborador->user?->role !== UserRole::Coordenadores) {
                $fail('O colaborador responsável deve ter perfil Coordenador.');
            }
        };
    }

    protected function atividadeColaboradorValidationRule(): Closure
    {
        return function (string $attribute, mixed $value, callable $fail): void {
            if (! $value) {
                return;
            }

            $colaborador = Colaborador::with('user')->find($value);
            if ($colaborador && $colaborador->user) {
                $allowedRoles = [
                    UserRole::Levantadores,
                    UserRole::Orcamentistas,
                    UserRole::Projetistas,
                ];
                if (! in_array($colaborador->user->role, $allowedRoles, true)) {
                    $fail('O colaborador deve ter perfil Levantador, Orçamentista ou Projetista.');
                }
            }
        };
    }

    /**
     * @param  array<string, mixed>  $atividadeData
     * @return array<string, mixed>
     */
    protected function buildAtividadePayload(array $atividadeData): array
    {
        return [
            'extensao_desenho' => $atividadeData['extensao_desenho'],
            'extensao_projeto' => $atividadeData['extensao_projeto'],
            'postes_desenhados' => $atividadeData['postes_desenhados'],
            'postes_projetados' => $atividadeData['postes_projetados'],
            'tipo_projeto' => $atividadeData['tipo_projeto'] ?? TipoProjetoAtividade::Cad->value,
            'duracao_horas' => $atividadeData['duracao_horas'] ?? '',
            'duracao_minutos' => $atividadeData['duracao_minutos'] ?? '',
            'observacoes' => $atividadeData['observacoes'] ?? '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formAtividadeFromModel(Atividade $atividade): array
    {
        return [
            'id' => $atividade->id,
            'nome' => $atividade->nome,
            'colaborador_id' => $atividade->colaborador_id,
            'extensao_desenho' => $atividade->extensao_desenho,
            'extensao_projeto' => $atividade->extensao_projeto,
            'postes_desenhados' => $atividade->postes_desenhados,
            'postes_projetados' => $atividade->postes_projetados,
            'tipo_projeto' => $atividade->tipo_projeto?->value ?? TipoProjetoAtividade::Cad->value,
            ...$this->duracaoFieldsFromMinutos($atividade->duracao_minutos),
            'observacoes' => $atividade->observacoes ?? '',
            '_delete' => false,
        ];
    }

    /**
     * @return array{duracao_horas: int|string, duracao_minutos: int|string}
     */
    protected function duracaoFieldsFromMinutos(?int $minutos): array
    {
        if ($minutos === null) {
            return [
                'duracao_horas' => '',
                'duracao_minutos' => '',
            ];
        }

        return [
            'duracao_horas' => intdiv($minutos, 60),
            'duracao_minutos' => $minutos % 60,
        ];
    }

    #[On('open-projeto-drawer')]
    public function open(?int $projetoId = null): void
    {
        if ($projetoId === null) {
            $this->authorize('create', Projeto::class);
            $this->resetForm();
            $this->showDrawer = true;

            return;
        }

        $projeto = Projeto::with('atividades')->findOrFail($projetoId);
        $this->authorize('view', $projeto);

        $this->editingProjetoId = $projeto->id;
        $this->nome = $projeto->nome;
        $this->colaboradorResponsavelId = $projeto->colaborador_responsavel_id;

        $this->atividades = $projeto->atividades->map(fn (Atividade $atividade) => $this->formAtividadeFromModel($atividade))->toArray();

        $this->atividadesLimite = 10;
        $this->showDrawer = true;
    }

    protected function refreshAtividadesFromProjeto(): void
    {
        if (! $this->editingProjetoId) {
            return;
        }

        $projeto = Projeto::with('atividades')->findOrFail($this->editingProjetoId);

        $this->atividades = $projeto->atividades->map(fn (Atividade $atividade) => $this->formAtividadeFromModel($atividade))->toArray();
    }

    #[Computed]
    public function editingProjeto(): ?Projeto
    {
        return $this->editingProjetoId ? Projeto::find($this->editingProjetoId) : null;
    }

    #[Computed]
    public function logAtividades()
    {
        if (! $this->editingProjetoId) {
            return collect();
        }

        return LogAtividade::query()
            ->where('projeto_id', $this->editingProjetoId)
            ->with(['user', 'atividade'])
            ->orderByDesc('created_at')
            ->limit($this->atividadesLimite)
            ->get();
    }

    public function carregarMaisAtividades(): void
    {
        $this->atividadesLimite += 10;
        unset($this->logAtividades);
    }

    public function nomeUsuarioAtividade(?User $user, int $userId): string
    {
        return $user?->name ?? "Usuário removido {$userId}";
    }

    public function nomeAtividadeLog(?Atividade $atividade, int $atividadeId): string
    {
        return $atividade?->nome ?? "Atividade removida {$atividadeId}";
    }

    public function labelItemAtividade(string $item): string
    {
        return $item === 'postes_desenhados' ? 'Postes Desenhados' : 'Postes Projetados';
    }

    #[Computed]
    public function coordenadoresDisponiveis(): array
    {
        return Colaborador::whereHas('user', fn ($q) => $q->role(UserRole::Coordenadores))
            ->with('user')
            ->orderBy('nome')
            ->get()
            ->mapWithKeys(fn (Colaborador $col) => [
                $col->id => $col->nome.' ('.$col->user?->email.')',
            ])
            ->toArray();
    }

    #[Computed]
    public function colaboradoresParaAtividades(): array
    {
        return Colaborador::whereHas('user', fn ($q) => $q->whereIn('role', [
            UserRole::Levantadores,
            UserRole::Orcamentistas,
            UserRole::Projetistas,
        ]))
            ->with('user')
            ->orderBy('nome')
            ->get()
            ->mapWithKeys(fn (Colaborador $col) => [
                $col->id => $col->nome.' ('.$col->user?->role->label().')',
            ])
            ->toArray();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function tiposProjetoDisponiveis(): array
    {
        return TipoProjetoAtividade::options();
    }

    public function addAtividade(CreateOrUpdateProjeto $projetoAction): void
    {
        $this->authorize('create', Atividade::class);

        if (! $this->editingProjetoId && empty($this->atividades)) {
            $this->validate([
                'nome' => ['required', 'string', 'max:255'],
                'colaboradorResponsavelId' => [
                    'required',
                    'exists:colaboradores,id',
                    $this->coordenadorValidationRule(),
                ],
            ]);

            $projeto = $projetoAction->create(
                $this->nome,
                $this->colaboradorResponsavelId
            );

            $this->editingProjetoId = $projeto->id;

            $this->swalToastSuccess([
                'title' => 'Projeto criado!',
                'showConfirmButton' => false,
                'position' => 'top-end',
                'timer' => 2000,
            ]);
        }

        $this->atividades[] = [
            'nome' => '',
            'colaborador_id' => null,
            'extensao_desenho' => 0,
            'extensao_projeto' => 0,
            'postes_desenhados' => 0,
            'postes_projetados' => 0,
            'tipo_projeto' => TipoProjetoAtividade::Cad->value,
            'duracao_horas' => '',
            'duracao_minutos' => '',
            'observacoes' => '',
            '_delete' => false,
        ];
    }

    public function saveAtividade(int $index, CreateOrUpdateAtividade $atividadeAction): void
    {
        if (! isset($this->atividades[$index])) {
            return;
        }

        if (! $this->editingProjetoId) {
            $this->swalToastWarning([
                'title' => 'Salve o projeto primeiro!',
                'text' => 'É necessário salvar o projeto antes de salvar atividades individualmente.',
                'showConfirmButton' => false,
                'position' => 'top-end',
                'timer' => 3000,
            ]);

            return;
        }

        $this->validate([
            "atividades.{$index}.nome" => ['required', 'string', 'max:255'],
            "atividades.{$index}.colaborador_id" => [
                'nullable',
                'exists:colaboradores,id',
                $this->atividadeColaboradorValidationRule(),
            ],
            "atividades.{$index}.extensao_desenho" => ['required', 'integer', 'min:0'],
            "atividades.{$index}.extensao_projeto" => ['required', 'integer', 'min:0'],
            "atividades.{$index}.postes_desenhados" => ['required', 'integer', 'min:0'],
            "atividades.{$index}.postes_projetados" => ['required', 'integer', 'min:0'],
            "atividades.{$index}.tipo_projeto" => ['required', Rule::enum(TipoProjetoAtividade::class)],
            "atividades.{$index}.duracao_horas" => ['nullable', 'integer', 'min:0'],
            "atividades.{$index}.duracao_minutos" => ['nullable', 'integer', 'min:0', 'max:59'],
            "atividades.{$index}.observacoes" => ['nullable', 'string'],
        ]);

        $atividadeData = $this->atividades[$index];

        if (isset($atividadeData['id'])) {
            $atividade = Atividade::findOrFail($atividadeData['id']);
            $this->authorize('update', $atividade);
            $this->validateAtividadeDuracao($index, $atividade);
        } else {
            $this->authorize('create', Atividade::class);
        }

        $user = auth()->user();

        DB::transaction(function () use ($atividadeData, $atividadeAction, $index, $user) {
            if (isset($atividadeData['id'])) {
                $atividade = Atividade::findOrFail($atividadeData['id']);
                $atividadeAction->update(
                    $atividade,
                    $atividadeData['nome'],
                    $atividadeData['colaborador_id'],
                    $this->buildAtividadePayload($atividadeData),
                    $user
                );
            } else {
                $novaAtividade = $atividadeAction->create(
                    $this->editingProjetoId,
                    $atividadeData['nome'],
                    $atividadeData['colaborador_id'],
                    $this->buildAtividadePayload($atividadeData),
                    $user
                );

                $this->atividades[$index]['id'] = $novaAtividade->id;
            }
        });

        $this->refreshAtividadesFromProjeto();

        $this->swalToastSuccess([
            'title' => 'Atividade salva!',
            'showConfirmButton' => false,
            'position' => 'top-end',
            'timer' => 2000,
        ]);
    }

    public ?int $removingAtividadeIndex = null;

    public function confirmRemoveAtividade(int $index): void
    {
        if (isset($this->atividades[$index]['id'])) {
            $atividade = Atividade::findOrFail($this->atividades[$index]['id']);
            $this->authorize('delete', $atividade);
        }

        $this->removingAtividadeIndex = $index;

        $componentId = $this->getId();
        $this->swalFire([
            'title' => 'Remover atividade?',
            'text' => 'Tem certeza que deseja remover esta atividade? Esta ação não pode ser desfeita.',
            'icon' => 'warning',
            'showCancelButton' => true,
            'confirmButtonText' => 'Sim, remover',
            'cancelButtonText' => 'Cancelar',
            'preConfirm' => "() => Livewire.find('{$componentId}').\$call('removeAtividadeConfirmed')",
        ]);
    }

    public function removeAtividadeConfirmed(): void
    {
        if ($this->removingAtividadeIndex === null) {
            return;
        }

        $index = $this->removingAtividadeIndex;

        if (isset($this->atividades[$index])) {
            if (isset($this->atividades[$index]['id'])) {
                $atividade = Atividade::findOrFail($this->atividades[$index]['id']);
                $this->authorize('delete', $atividade);
                $atividade->delete();
            }

            unset($this->atividades[$index]);
            $this->atividades = array_values($this->atividades);
        }

        $this->removingAtividadeIndex = null;
        $this->refreshAtividadesFromProjeto();

        $this->swalToastWarning([
            'title' => 'Atividade removida!',
            'showConfirmButton' => false,
            'position' => 'top-end',
            'timer' => 2000,
        ]);
    }

    public function removeAtividade(int $index): void
    {
        if (isset($this->atividades[$index])) {
            if (isset($this->atividades[$index]['id'])) {
                $this->atividades[$index]['_delete'] = true;
            } else {
                unset($this->atividades[$index]);
                $this->atividades = array_values($this->atividades);
            }
        }
    }

    public function save(CreateOrUpdateProjeto $projetoAction, CreateOrUpdateAtividade $atividadeAction): void
    {
        $this->validate();

        if ($this->editingProjetoId) {
            $projeto = Projeto::findOrFail($this->editingProjetoId);
            $this->authorize('update', $projeto);
        } else {
            $this->authorize('create', Projeto::class);
        }

        foreach ($this->atividades as $index => $atividadeData) {
            if ($atividadeData['_delete'] ?? false) {
                continue;
            }
            if (isset($atividadeData['id'])) {
                $this->validateAtividadeDuracao($index, Atividade::findOrFail($atividadeData['id']));
            }
        }

        $user = auth()->user();

        DB::transaction(function () use ($projetoAction, $atividadeAction, $user) {
            if ($this->editingProjetoId) {
                $projeto = Projeto::findOrFail($this->editingProjetoId);
                $projeto = $projetoAction->update(
                    $projeto,
                    $this->nome,
                    $this->colaboradorResponsavelId
                );
            } else {
                $projeto = $projetoAction->create(
                    $this->nome,
                    $this->colaboradorResponsavelId
                );
            }

            foreach ($this->atividades as $atividadeData) {
                if ($atividadeData['_delete'] ?? false) {
                    if (isset($atividadeData['id'])) {
                        $atividade = Atividade::findOrFail($atividadeData['id']);
                        $this->authorize('delete', $atividade);
                        $atividade->delete();
                    }
                } elseif (isset($atividadeData['id'])) {
                    $atividade = Atividade::findOrFail($atividadeData['id']);
                    $this->authorize('update', $atividade);
                    $atividadeAction->update(
                        $atividade,
                        $atividadeData['nome'],
                        $atividadeData['colaborador_id'],
                        $this->buildAtividadePayload($atividadeData),
                        $user
                    );
                } else {
                    $this->authorize('create', Atividade::class);
                    $atividadeAction->create(
                        $projeto->id,
                        $atividadeData['nome'],
                        $atividadeData['colaborador_id'],
                        $this->buildAtividadePayload($atividadeData),
                        $user
                    );
                }
            }
        });

        $this->swalToastSuccess([
            'title' => 'Salvo com sucesso!',
            'showConfirmButton' => false,
            'position' => 'top-end',
            'timer' => 2000,
        ]);

        $this->dispatch('projeto-saved');
        $this->closeDrawer();
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->nome = '';
        $this->colaboradorResponsavelId = null;
        $this->atividades = [];
        $this->editingProjetoId = null;
        $this->atividadesLimite = 10;
        $this->resetValidation();
    }

    protected function validateAtividadeDuracao(int $index, Atividade $dbAtividade): void
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return;
        }

        $user->loadMissing('colaborador');

        $isAdminOrCoord = $user->isAdminOrSuperAdmin() || $user->isCoordenador();
        $isAssignedCollaborator = $dbAtividade->colaborador_id === $user->colaborador?->id;

        if (! $isAssignedCollaborator || $isAdminOrCoord) {
            return;
        }

        $atividadeRow = $this->atividades[$index] ?? [];
        $horas = $atividadeRow['duracao_horas'] ?? null;
        $minutos = $atividadeRow['duracao_minutos'] ?? null;
        $keyHoras = "atividades.{$index}.duracao_horas";

        $totalMinutos = ((int) $horas * 60) + (int) $minutos;

        if ($totalMinutos <= 0) {
            throw ValidationException::withMessages([
                $keyHoras => 'Informe a duração maior que zero.',
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.admin.projeto-edit-drawer');
    }
}
