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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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

    /**
     * @var array<int, string>
     */
    public array $atividadesDataHoraInicio = [];

    /**
     * @var array<int, string>
     */
    public array $atividadesDataHoraFim = [];

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
            'atividades.*.data_hora_inicio' => ['nullable', 'string', 'max:32'],
            'atividades.*.data_hora_fim' => ['nullable', 'string', 'max:32'],
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
            'data_hora_inicio' => $atividadeData['data_hora_inicio'] ?? '',
            'data_hora_fim' => $atividadeData['data_hora_fim'] ?? '',
            'observacoes' => $atividadeData['observacoes'] ?? '',
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

        $this->atividades = $projeto->atividades->map(fn (Atividade $atividade) => [
            'id' => $atividade->id,
            'nome' => $atividade->nome,
            'colaborador_id' => $atividade->colaborador_id,
            'extensao_desenho' => $atividade->extensao_desenho,
            'extensao_projeto' => $atividade->extensao_projeto,
            'postes_desenhados' => $atividade->postes_desenhados,
            'postes_projetados' => $atividade->postes_projetados,
            'tipo_projeto' => $atividade->tipo_projeto?->value ?? TipoProjetoAtividade::Cad->value,
            'data_hora_inicio' => $atividade->data_hora_inicio?->format('Y-m-d\TH:i') ?? '',
            'data_hora_fim' => $atividade->data_hora_fim?->format('Y-m-d\TH:i') ?? '',
            'observacoes' => $atividade->observacoes ?? '',
            '_delete' => false,
        ])->toArray();

        $this->syncAtividadesDatetimesToParallel();

        $this->atividadesLimite = 10;
        $this->showDrawer = true;
    }

    protected function refreshAtividadesFromProjeto(): void
    {
        if (! $this->editingProjetoId) {
            return;
        }

        $projeto = Projeto::with('atividades')->findOrFail($this->editingProjetoId);

        $this->atividades = $projeto->atividades->map(fn (Atividade $atividade) => [
            'id' => $atividade->id,
            'nome' => $atividade->nome,
            'colaborador_id' => $atividade->colaborador_id,
            'extensao_desenho' => $atividade->extensao_desenho,
            'extensao_projeto' => $atividade->extensao_projeto,
            'postes_desenhados' => $atividade->postes_desenhados,
            'postes_projetados' => $atividade->postes_projetados,
            'tipo_projeto' => $atividade->tipo_projeto?->value ?? TipoProjetoAtividade::Cad->value,
            'data_hora_inicio' => $atividade->data_hora_inicio?->format('Y-m-d\TH:i') ?? '',
            'data_hora_fim' => $atividade->data_hora_fim?->format('Y-m-d\TH:i') ?? '',
            'observacoes' => $atividade->observacoes ?? '',
            '_delete' => false,
        ])->toArray();

        $this->syncAtividadesDatetimesToParallel();
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
            'data_hora_inicio' => '',
            'data_hora_fim' => '',
            'observacoes' => '',
            '_delete' => false,
        ];

        $this->atividadesDataHoraInicio[] = '';
        $this->atividadesDataHoraFim[] = '';
    }

    public function saveAtividade(int $index, CreateOrUpdateAtividade $atividadeAction): void
    {
        if (! isset($this->atividades[$index])) {
            return;
        }

        $this->syncAtividadesDatetimesFromParallel();

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
            "atividades.{$index}.data_hora_inicio" => ['nullable', 'string', 'max:32'],
            "atividades.{$index}.data_hora_fim" => ['nullable', 'string', 'max:32'],
            "atividades.{$index}.observacoes" => ['nullable', 'string'],
        ]);

        $atividadeData = $this->atividades[$index];

        if (isset($atividadeData['id'])) {
            $atividade = Atividade::findOrFail($atividadeData['id']);
            $this->authorize('update', $atividade);
            $this->validateAtividadeDatetimeFields($index, $atividade);
        } else {
            $this->authorize('create', Atividade::class);
        }

        $user = auth()->user();

        DB::transaction(function () use ($atividadeData, $atividadeAction, $index, $user) {
            if (isset($atividadeData['id'])) {
                $atividade = Atividade::findOrFail($atividadeData['id']);
                $atividadeAtualizada = $atividadeAction->update(
                    $atividade,
                    $atividadeData['nome'],
                    $atividadeData['colaborador_id'],
                    $this->buildAtividadePayload($atividadeData),
                    $user
                );

                $this->atividades[$index]['data_hora_inicio'] = $atividadeAtualizada->data_hora_inicio?->format('Y-m-d\TH:i') ?? '';
                $this->atividades[$index]['data_hora_fim'] = $atividadeAtualizada->data_hora_fim?->format('Y-m-d\TH:i') ?? '';
                $this->atividadesDataHoraInicio[$index] = $this->atividades[$index]['data_hora_inicio'];
                $this->atividadesDataHoraFim[$index] = $this->atividades[$index]['data_hora_fim'];
            } else {
                $novaAtividade = $atividadeAction->create(
                    $this->editingProjetoId,
                    $atividadeData['nome'],
                    $atividadeData['colaborador_id'],
                    $this->buildAtividadePayload($atividadeData),
                    $user
                );

                $this->atividades[$index]['id'] = $novaAtividade->id;
                $this->atividadesDataHoraInicio[$index] = '';
                $this->atividadesDataHoraFim[$index] = '';
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
            $this->syncAtividadesDatetimesToParallel();
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
                $this->syncAtividadesDatetimesToParallel();
            }
        }
    }

    public function save(CreateOrUpdateProjeto $projetoAction, CreateOrUpdateAtividade $atividadeAction): void
    {
        $this->syncAtividadesDatetimesFromParallel();

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
                $this->validateAtividadeDatetimeFields($index, Atividade::findOrFail($atividadeData['id']));
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
        $this->atividadesDataHoraInicio = [];
        $this->atividadesDataHoraFim = [];
        $this->editingProjetoId = null;
        $this->atividadesLimite = 10;
        $this->resetValidation();
    }

    protected function syncAtividadesDatetimesFromParallel(): void
    {
        foreach ($this->atividades as $i => $atividadeRow) {
            $this->atividades[$i]['data_hora_inicio'] = $this->atividadesDataHoraInicio[$i] ?? ($atividadeRow['data_hora_inicio'] ?? '');
            $this->atividades[$i]['data_hora_fim'] = $this->atividadesDataHoraFim[$i] ?? ($atividadeRow['data_hora_fim'] ?? '');
        }
    }

    protected function syncAtividadesDatetimesToParallel(): void
    {
        $this->atividadesDataHoraInicio = [];
        $this->atividadesDataHoraFim = [];
        foreach ($this->atividades as $i => $atividadeRow) {
            $this->atividadesDataHoraInicio[$i] = $atividadeRow['data_hora_inicio'] ?? '';
            $this->atividadesDataHoraFim[$i] = $atividadeRow['data_hora_fim'] ?? '';
        }
    }

    protected function validateAtividadeDatetimeFields(int $index, Atividade $dbAtividade): void
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return;
        }

        $user->loadMissing('colaborador');

        $atividadeRow = $this->atividades[$index] ?? [];
        $inicio = $this->normalizeDatetimeInput($atividadeRow['data_hora_inicio'] ?? null);
        $fim = $this->normalizeDatetimeInput($atividadeRow['data_hora_fim'] ?? null);

        $inicioDb = $dbAtividade->data_hora_inicio !== null;
        $fimDb = $dbAtividade->data_hora_fim !== null;
        $hasBothStored = $inicioDb && $fimDb;
        $hasNeitherStored = ! $inicioDb && ! $fimDb;

        $isAdminOrCoord = $user->isAdminOrSuperAdmin() || $user->isCoordenador();
        $isAssignedCollaborator = $dbAtividade->colaborador_id === $user->colaborador?->id;

        $keyInicio = "atividades.{$index}.data_hora_inicio";
        $keyFim = "atividades.{$index}.data_hora_fim";
        $validationData = [
            'atividades' => [
                $index => [
                    'data_hora_inicio' => $inicio,
                    'data_hora_fim' => $fim,
                ],
            ],
        ];

        if ($hasNeitherStored) {
            if ($isAdminOrCoord) {
                return;
            }

            if ($isAssignedCollaborator) {
                Validator::make(
                    $validationData,
                    [
                        $keyInicio => ['required', 'date'],
                        $keyFim => ['required', 'date', 'after:'.$keyInicio],
                    ],
                    [
                        $keyInicio.'.required' => 'Informe a data e hora de início.',
                        $keyFim.'.required' => 'Informe a data e hora de fim.',
                        $keyFim.'.after' => 'A data e hora de fim deve ser posterior à de início.',
                    ]
                )->validate();
            }

            return;
        }

        if ($hasBothStored && $isAssignedCollaborator && ! $isAdminOrCoord) {
            return;
        }

        if ($isAssignedCollaborator && ! $isAdminOrCoord) {
            return;
        }

        if ($isAdminOrCoord) {
            Validator::make(
                $validationData,
                [
                    $keyInicio => ['required_with:'.$keyFim, 'nullable', 'date'],
                    $keyFim => ['required_with:'.$keyInicio, 'nullable', 'date', 'after:'.$keyInicio],
                ],
                [
                    $keyInicio.'.required_with' => 'Ao informar uma data, preencha também a outra.',
                    $keyFim.'.required_with' => 'Ao informar uma data, preencha também a outra.',
                    $keyFim.'.after' => 'A data e hora de fim deve ser posterior à de início.',
                ]
            )->validate();
        }
    }

    protected function normalizeDatetimeInput(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    public function render(): View
    {
        return view('livewire.admin.projeto-edit-drawer');
    }
}
