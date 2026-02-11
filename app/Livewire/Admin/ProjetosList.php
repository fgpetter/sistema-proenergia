<?php

namespace App\Livewire\Admin;

use App\Actions\CreateOrUpdateParte;
use App\Actions\CreateOrUpdateProjeto;
use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\Parte;
use App\Models\Projeto;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
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

    public bool $showDrawer = false;

    public ?int $editingProjetoId = null;

    public string $nome = '';

    public ?int $colaboradorResponsavelId = null;

    public array $partes = [];

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public bool $showRemoveParteModal = false;

    public ?int $removingParteIndex = null;

    protected function rules(): array
    {
        $rules = [
            'nome' => ['required', 'string', 'max:255'],
            'colaboradorResponsavelId' => [
                'required',
                'exists:colaboradores,id',
                function ($attribute, $value, $fail) {
                    $colaborador = Colaborador::with('user')->find($value);
                    if ($colaborador && $colaborador->user?->role !== UserRole::Coordenadores) {
                        $fail('O colaborador responsável deve ter perfil Coordenador.');
                    }
                },
            ],
            'partes.*.nome' => ['required', 'string', 'max:255'],
            'partes.*.colaborador_id' => [
                'nullable',
                'exists:colaboradores,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
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
                    }
                },
            ],
            'partes.*.extensao_desenho' => ['required', 'integer', 'min:0'],
            'partes.*.extensao_projeto' => ['required', 'integer', 'min:0'],
            'partes.*.postes_desenhados' => ['required', 'integer', 'min:0'],
            'partes.*.postes_projetados' => ['required', 'integer', 'min:0'],
        ];

        return $rules;
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
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
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

    public function openCreateDrawer(): void
    {
        $this->authorize('create', Projeto::class);
        $this->resetForm();
        $this->editingProjetoId = null;
        $this->showDrawer = true;
    }

    public function openEditDrawer(int $projetoId): void
    {
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
            '_delete' => false,
        ])->toArray();

        $this->showDrawer = true;
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
                    function ($attribute, $value, $fail) {
                        $colaborador = Colaborador::with('user')->find($value);
                        if ($colaborador && $colaborador->user?->role !== UserRole::Coordenadores) {
                            $fail('O colaborador responsável deve ter perfil Coordenador.');
                        }
                    },
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
            '_delete' => false,
        ];
    }

    public function saveParte(int $index, CreateOrUpdateParte $parteAction): void
    {
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
                function ($attribute, $value, $fail) {
                    if ($value) {
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
                    }
                },
            ],
            "partes.{$index}.extensao_desenho" => ['required', 'integer', 'min:0'],
            "partes.{$index}.extensao_projeto" => ['required', 'integer', 'min:0'],
            "partes.{$index}.postes_desenhados" => ['required', 'integer', 'min:0'],
            "partes.{$index}.postes_projetados" => ['required', 'integer', 'min:0'],
        ]);

        $parteData = $this->partes[$index];

        if (isset($parteData['id'])) {
            $parte = Parte::findOrFail($parteData['id']);
            $this->authorize('update', $parte);
        } else {
            $this->authorize('create', Parte::class);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($parteData, $parteAction, $index) {
            if (isset($parteData['id'])) {
                $parte = Parte::findOrFail($parteData['id']);
                $parteAction->update(
                    $parte,
                    $parteData['nome'],
                    $parteData['colaborador_id'],
                    [
                        'extensao_desenho' => $parteData['extensao_desenho'],
                        'extensao_projeto' => $parteData['extensao_projeto'],
                        'postes_desenhados' => $parteData['postes_desenhados'],
                        'postes_projetados' => $parteData['postes_projetados'],
                    ]
                );
            } else {
                $novaParte = $parteAction->create(
                    $this->editingProjetoId,
                    $parteData['nome'],
                    $parteData['colaborador_id'],
                    [
                        'extensao_desenho' => $parteData['extensao_desenho'],
                        'extensao_projeto' => $parteData['extensao_projeto'],
                        'postes_desenhados' => $parteData['postes_desenhados'],
                        'postes_projetados' => $parteData['postes_projetados'],
                    ]
                );

                $this->partes[$index]['id'] = $novaParte->id;
            }
        });

        $this->swalToastSuccess([
            'title' => 'Parte salva!',
            'showConfirmButton' => false,
            'position' => 'top-end',
            'timer' => 2000,
        ]);
    }

    public function confirmRemoveParte(int $index): void
    {
        if (isset($this->partes[$index]['id'])) {
            $parte = Parte::findOrFail($this->partes[$index]['id']);
            $this->authorize('delete', $parte);
        }
        $this->removingParteIndex = $index;
        $this->showRemoveParteModal = true;
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
        }

        $this->swalToastWarning([
            'title' => 'Parte removida!',
            'showConfirmButton' => false,
            'position' => 'top-end',
            'timer' => 2000,
        ]);

        $this->closeRemoveParteModal();
    }

    public function closeRemoveParteModal(): void
    {
        $this->showRemoveParteModal = false;
        $this->removingParteIndex = null;
    }

    public function removeParte(int $index): void
    {
        if (isset($this->partes[$index])) {
            if (isset($this->partes[$index]['id'])) {
                $this->partes[$index]['_delete'] = true;
            } else {
                unset($this->partes[$index]);
                $this->partes = array_values($this->partes);
            }
        }
    }

    public function save(CreateOrUpdateProjeto $projetoAction, CreateOrUpdateParte $parteAction): void
    {
        $this->validate();

        if ($this->editingProjetoId) {
            $projeto = Projeto::findOrFail($this->editingProjetoId);
            $this->authorize('update', $projeto);
        } else {
            $this->authorize('create', Projeto::class);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($projetoAction, $parteAction) {
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
                        [
                            'extensao_desenho' => $parteData['extensao_desenho'],
                            'extensao_projeto' => $parteData['extensao_projeto'],
                            'postes_desenhados' => $parteData['postes_desenhados'],
                            'postes_projetados' => $parteData['postes_projetados'],
                        ]
                    );
                } else {
                    $this->authorize('create', Parte::class);
                    $parteAction->create(
                        $projeto->id,
                        $parteData['nome'],
                        $parteData['colaborador_id'],
                        [
                            'extensao_desenho' => $parteData['extensao_desenho'],
                            'extensao_projeto' => $parteData['extensao_projeto'],
                            'postes_desenhados' => $parteData['postes_desenhados'],
                            'postes_projetados' => $parteData['postes_projetados'],
                        ]
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

        $this->closeDrawer();
    }

    public function confirmDelete(int $projetoId): void
    {
        $projeto = Projeto::findOrFail($projetoId);
        $this->authorize('delete', $projeto);
        $this->deletingId = $projetoId;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $projeto = Projeto::findOrFail($this->deletingId);
        $this->authorize('delete', $projeto);
        $projeto->delete();

        $this->swalToastWarning([
            'title' => 'Excluído com sucesso!',
            'showConfirmButton' => false,
            'position' => 'top-end',
            'timer' => 2000,
        ]);

        $this->closeDeleteModal();
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
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
        $this->colaboradorResponsavelId = null;
        $this->partes = [];
        $this->editingProjetoId = null;
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.admin.projetos-list');
    }
}
