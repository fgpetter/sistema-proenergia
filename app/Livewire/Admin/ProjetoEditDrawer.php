<?php

namespace App\Livewire\Admin;

use App\Actions\CreateOrUpdateParte;
use App\Actions\CreateOrUpdateProjeto;
use App\Enums\TipoProjetoParte;
use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\Parte;
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

    public string $nome = '';

    public ?int $colaboradorResponsavelId = null;

    public array $partes = [];

    /**
     * @var array<int, string>
     */
    public array $partesDataHoraInicio = [];

    /**
     * @var array<int, string>
     */
    public array $partesDataHoraFim = [];

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'colaboradorResponsavelId' => [
                'required',
                'exists:colaboradores,id',
                $this->coordenadorValidationRule(),
            ],
            'partes.*.nome' => ['required', 'string', 'max:255'],
            'partes.*.colaborador_id' => [
                'nullable',
                'exists:colaboradores,id',
                $this->parteColaboradorValidationRule(),
            ],
            'partes.*.extensao_desenho' => ['required', 'integer', 'min:0'],
            'partes.*.extensao_projeto' => ['required', 'integer', 'min:0'],
            'partes.*.postes_desenhados' => ['required', 'integer', 'min:0'],
            'partes.*.postes_projetados' => ['required', 'integer', 'min:0'],
            'partes.*.tipo_projeto' => ['required', Rule::enum(TipoProjetoParte::class)],
            'partes.*.data_hora_inicio' => ['nullable', 'string', 'max:32'],
            'partes.*.data_hora_fim' => ['nullable', 'string', 'max:32'],
            'partes.*.observacoes' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nome.required' => 'O nome do projeto é obrigatório.',
            'colaboradorResponsavelId.required' => 'O responsável é obrigatório.',
            'partes.*.nome.required' => 'O nome da parte é obrigatório.',
            'partes.*.extensao_desenho.required' => 'A extensão de desenho é obrigatória.',
            'partes.*.extensao_projeto.required' => 'A extensão de projeto é obrigatória.',
            'partes.*.postes_desenhados.required' => 'O número de postes desenhados é obrigatório.',
            'partes.*.postes_projetados.required' => 'O número de postes projetados é obrigatório.',
            'partes.*.tipo_projeto.required' => 'O tipo de projeto é obrigatório.',
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

    protected function parteColaboradorValidationRule(): Closure
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
     * @param  array<string, mixed>  $parteData
     * @return array<string, mixed>
     */
    protected function buildPartePayload(array $parteData): array
    {
        return [
            'extensao_desenho' => $parteData['extensao_desenho'],
            'extensao_projeto' => $parteData['extensao_projeto'],
            'postes_desenhados' => $parteData['postes_desenhados'],
            'postes_projetados' => $parteData['postes_projetados'],
            'tipo_projeto' => $parteData['tipo_projeto'] ?? TipoProjetoParte::Cad->value,
            'data_hora_inicio' => $parteData['data_hora_inicio'] ?? '',
            'data_hora_fim' => $parteData['data_hora_fim'] ?? '',
            'observacoes' => $parteData['observacoes'] ?? '',
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

        $projeto = Projeto::with('partes')->findOrFail($projetoId);
        $this->authorize('view', $projeto);

        $this->editingProjetoId = $projeto->id;
        $this->nome = $projeto->nome;
        $this->colaboradorResponsavelId = $projeto->colaborador_responsavel_id;

        $this->partes = $projeto->partes->map(fn (Parte $parte) => [
            'id' => $parte->id,
            'nome' => $parte->nome,
            'colaborador_id' => $parte->colaborador_id,
            'extensao_desenho' => $parte->extensao_desenho,
            'extensao_projeto' => $parte->extensao_projeto,
            'postes_desenhados' => $parte->postes_desenhados,
            'postes_projetados' => $parte->postes_projetados,
            'tipo_projeto' => $parte->tipo_projeto?->value ?? TipoProjetoParte::Cad->value,
            'data_hora_inicio' => $parte->data_hora_inicio?->format('Y-m-d\TH:i') ?? '',
            'data_hora_fim' => $parte->data_hora_fim?->format('Y-m-d\TH:i') ?? '',
            'observacoes' => $parte->observacoes ?? '',
            '_delete' => false,
        ])->toArray();

        $this->syncPartesDatetimesToParallel();

        $this->showDrawer = true;
    }

    protected function refreshPartesFromProjeto(): void
    {
        if (! $this->editingProjetoId) {
            return;
        }

        $projeto = Projeto::with('partes')->findOrFail($this->editingProjetoId);

        $this->partes = $projeto->partes->map(fn (Parte $parte) => [
            'id' => $parte->id,
            'nome' => $parte->nome,
            'colaborador_id' => $parte->colaborador_id,
            'extensao_desenho' => $parte->extensao_desenho,
            'extensao_projeto' => $parte->extensao_projeto,
            'postes_desenhados' => $parte->postes_desenhados,
            'postes_projetados' => $parte->postes_projetados,
            'tipo_projeto' => $parte->tipo_projeto?->value ?? TipoProjetoParte::Cad->value,
            'data_hora_inicio' => $parte->data_hora_inicio?->format('Y-m-d\TH:i') ?? '',
            'data_hora_fim' => $parte->data_hora_fim?->format('Y-m-d\TH:i') ?? '',
            'observacoes' => $parte->observacoes ?? '',
            '_delete' => false,
        ])->toArray();

        $this->syncPartesDatetimesToParallel();
    }

    #[Computed]
    public function editingProjeto(): ?Projeto
    {
        return $this->editingProjetoId ? Projeto::find($this->editingProjetoId) : null;
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
    public function colaboradoresParaPartes(): array
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
        return TipoProjetoParte::options();
    }

    public function addParte(CreateOrUpdateProjeto $projetoAction): void
    {
        $this->authorize('create', Parte::class);

        if (! $this->editingProjetoId && empty($this->partes)) {
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

        $this->partes[] = [
            'nome' => '',
            'colaborador_id' => null,
            'extensao_desenho' => 0,
            'extensao_projeto' => 0,
            'postes_desenhados' => 0,
            'postes_projetados' => 0,
            'tipo_projeto' => TipoProjetoParte::Cad->value,
            'data_hora_inicio' => '',
            'data_hora_fim' => '',
            'observacoes' => '',
            '_delete' => false,
        ];

        $this->partesDataHoraInicio[] = '';
        $this->partesDataHoraFim[] = '';
    }

    public function saveParte(int $index, CreateOrUpdateParte $parteAction): void
    {
        if (! isset($this->partes[$index])) {
            return;
        }

        $this->syncPartesDatetimesFromParallel();

        if (! $this->editingProjetoId) {
            $this->swalToastWarning([
                'title' => 'Salve o projeto primeiro!',
                'text' => 'É necessário salvar o projeto antes de salvar partes individualmente.',
                'showConfirmButton' => false,
                'position' => 'top-end',
                'timer' => 3000,
            ]);

            return;
        }

        $this->validate([
            "partes.{$index}.nome" => ['required', 'string', 'max:255'],
            "partes.{$index}.colaborador_id" => [
                'nullable',
                'exists:colaboradores,id',
                $this->parteColaboradorValidationRule(),
            ],
            "partes.{$index}.extensao_desenho" => ['required', 'integer', 'min:0'],
            "partes.{$index}.extensao_projeto" => ['required', 'integer', 'min:0'],
            "partes.{$index}.postes_desenhados" => ['required', 'integer', 'min:0'],
            "partes.{$index}.postes_projetados" => ['required', 'integer', 'min:0'],
            "partes.{$index}.tipo_projeto" => ['required', Rule::enum(TipoProjetoParte::class)],
            "partes.{$index}.data_hora_inicio" => ['nullable', 'string', 'max:32'],
            "partes.{$index}.data_hora_fim" => ['nullable', 'string', 'max:32'],
            "partes.{$index}.observacoes" => ['nullable', 'string'],
        ]);

        $parteData = $this->partes[$index];

        if (isset($parteData['id'])) {
            $parte = Parte::findOrFail($parteData['id']);
            $this->authorize('update', $parte);
            $this->validateParteDatetimeFields($index, $parte);
        } else {
            $this->authorize('create', Parte::class);
        }

        $user = auth()->user();

        DB::transaction(function () use ($parteData, $parteAction, $index, $user) {
            if (isset($parteData['id'])) {
                $parte = Parte::findOrFail($parteData['id']);
                $parteAtualizada = $parteAction->update(
                    $parte,
                    $parteData['nome'],
                    $parteData['colaborador_id'],
                    $this->buildPartePayload($parteData),
                    $user
                );

                $this->partes[$index]['data_hora_inicio'] = $parteAtualizada->data_hora_inicio?->format('Y-m-d\TH:i') ?? '';
                $this->partes[$index]['data_hora_fim'] = $parteAtualizada->data_hora_fim?->format('Y-m-d\TH:i') ?? '';
                $this->partesDataHoraInicio[$index] = $this->partes[$index]['data_hora_inicio'];
                $this->partesDataHoraFim[$index] = $this->partes[$index]['data_hora_fim'];
            } else {
                $novaParte = $parteAction->create(
                    $this->editingProjetoId,
                    $parteData['nome'],
                    $parteData['colaborador_id'],
                    $this->buildPartePayload($parteData),
                    $user
                );

                $this->partes[$index]['id'] = $novaParte->id;
                $this->partesDataHoraInicio[$index] = '';
                $this->partesDataHoraFim[$index] = '';
            }
        });

        $this->refreshPartesFromProjeto();

        $this->swalToastSuccess([
            'title' => 'Parte salva!',
            'showConfirmButton' => false,
            'position' => 'top-end',
            'timer' => 2000,
        ]);
    }

    public ?int $removingParteIndex = null;

    public function confirmRemoveParte(int $index): void
    {
        if (isset($this->partes[$index]['id'])) {
            $parte = Parte::findOrFail($this->partes[$index]['id']);
            $this->authorize('delete', $parte);
        }

        $this->removingParteIndex = $index;

        $componentId = $this->getId();
        $this->swalFire([
            'title' => 'Remover parte?',
            'text' => 'Tem certeza que deseja remover esta parte? Esta ação não pode ser desfeita.',
            'icon' => 'warning',
            'showCancelButton' => true,
            'confirmButtonText' => 'Sim, remover',
            'cancelButtonText' => 'Cancelar',
            'preConfirm' => "() => Livewire.find('{$componentId}').\$call('removeParteConfirmed')",
        ]);
    }

    public function removeParteConfirmed(): void
    {
        if ($this->removingParteIndex === null) {
            return;
        }

        $index = $this->removingParteIndex;

        if (isset($this->partes[$index])) {
            if (isset($this->partes[$index]['id'])) {
                $parte = Parte::findOrFail($this->partes[$index]['id']);
                $this->authorize('delete', $parte);
                $parte->delete();
            }

            unset($this->partes[$index]);
            $this->partes = array_values($this->partes);
            $this->syncPartesDatetimesToParallel();
        }

        $this->removingParteIndex = null;
        $this->refreshPartesFromProjeto();

        $this->swalToastWarning([
            'title' => 'Parte removida!',
            'showConfirmButton' => false,
            'position' => 'top-end',
            'timer' => 2000,
        ]);
    }

    public function removeParte(int $index): void
    {
        if (isset($this->partes[$index])) {
            if (isset($this->partes[$index]['id'])) {
                $this->partes[$index]['_delete'] = true;
            } else {
                unset($this->partes[$index]);
                $this->partes = array_values($this->partes);
                $this->syncPartesDatetimesToParallel();
            }
        }
    }

    public function save(CreateOrUpdateProjeto $projetoAction, CreateOrUpdateParte $parteAction): void
    {
        $this->syncPartesDatetimesFromParallel();

        $this->validate();

        if ($this->editingProjetoId) {
            $projeto = Projeto::findOrFail($this->editingProjetoId);
            $this->authorize('update', $projeto);
        } else {
            $this->authorize('create', Projeto::class);
        }

        foreach ($this->partes as $index => $parteData) {
            if ($parteData['_delete'] ?? false) {
                continue;
            }
            if (isset($parteData['id'])) {
                $this->validateParteDatetimeFields($index, Parte::findOrFail($parteData['id']));
            }
        }

        $user = auth()->user();

        DB::transaction(function () use ($projetoAction, $parteAction, $user) {
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

            foreach ($this->partes as $parteData) {
                if ($parteData['_delete'] ?? false) {
                    if (isset($parteData['id'])) {
                        $parte = Parte::findOrFail($parteData['id']);
                        $this->authorize('delete', $parte);
                        $parte->delete();
                    }
                } elseif (isset($parteData['id'])) {
                    $parte = Parte::findOrFail($parteData['id']);
                    $this->authorize('update', $parte);
                    $parteAction->update(
                        $parte,
                        $parteData['nome'],
                        $parteData['colaborador_id'],
                        $this->buildPartePayload($parteData),
                        $user
                    );
                } else {
                    $this->authorize('create', Parte::class);
                    $parteAction->create(
                        $projeto->id,
                        $parteData['nome'],
                        $parteData['colaborador_id'],
                        $this->buildPartePayload($parteData),
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
        $this->partes = [];
        $this->partesDataHoraInicio = [];
        $this->partesDataHoraFim = [];
        $this->editingProjetoId = null;
        $this->resetValidation();
    }

    protected function syncPartesDatetimesFromParallel(): void
    {
        foreach ($this->partes as $i => $parteRow) {
            $this->partes[$i]['data_hora_inicio'] = $this->partesDataHoraInicio[$i] ?? ($parteRow['data_hora_inicio'] ?? '');
            $this->partes[$i]['data_hora_fim'] = $this->partesDataHoraFim[$i] ?? ($parteRow['data_hora_fim'] ?? '');
        }
    }

    protected function syncPartesDatetimesToParallel(): void
    {
        $this->partesDataHoraInicio = [];
        $this->partesDataHoraFim = [];
        foreach ($this->partes as $i => $parteRow) {
            $this->partesDataHoraInicio[$i] = $parteRow['data_hora_inicio'] ?? '';
            $this->partesDataHoraFim[$i] = $parteRow['data_hora_fim'] ?? '';
        }
    }

    protected function validateParteDatetimeFields(int $index, Parte $dbParte): void
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return;
        }

        $user->loadMissing('colaborador');

        $parteRow = $this->partes[$index] ?? [];
        $inicio = $this->normalizeDatetimeInput($parteRow['data_hora_inicio'] ?? null);
        $fim = $this->normalizeDatetimeInput($parteRow['data_hora_fim'] ?? null);

        $inicioDb = $dbParte->data_hora_inicio !== null;
        $fimDb = $dbParte->data_hora_fim !== null;
        $hasBothStored = $inicioDb && $fimDb;
        $hasNeitherStored = ! $inicioDb && ! $fimDb;

        $isAdminOrCoord = $user->isAdminOrSuperAdmin() || $user->isCoordenador();
        $isAssignedCollaborator = $dbParte->colaborador_id === $user->colaborador?->id;

        $keyInicio = "partes.{$index}.data_hora_inicio";
        $keyFim = "partes.{$index}.data_hora_fim";
        $validationData = [
            'partes' => [
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
