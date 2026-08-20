<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\Projeto;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjetoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'colaborador_responsavel_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $colaborador = Colaborador::withTrashed()
                        ->with(['user' => fn ($query) => $query->withTrashed()])
                        ->find($value);

                    if (! $colaborador) {
                        $fail('O colaborador selecionado não existe.');

                        return;
                    }

                    if ($colaborador->trashed()) {
                        $projeto = $this->route('projeto');
                        $atribuicaoAtualId = $projeto instanceof Projeto
                            ? $projeto->colaborador_responsavel_id
                            : null;

                        if ($atribuicaoAtualId === null || (int) $atribuicaoAtualId !== (int) $value) {
                            $fail('O colaborador selecionado não está disponível.');
                        }

                        return;
                    }

                    if ($colaborador->user?->role !== UserRole::Coordenadores) {
                        $fail('O colaborador responsável deve ter perfil Coordenador.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do projeto é obrigatório.',
            'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
            'colaborador_responsavel_id.required' => 'O responsável é obrigatório.',
            'colaborador_responsavel_id.exists' => 'O colaborador selecionado não existe.',
        ];
    }
}
