<?php

namespace App\Http\Requests;

use App\Enums\TipoProjetoParte;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'projeto_id' => ['required', 'exists:projetos,id'],
            'colaborador_id' => [
                'nullable',
                'exists:colaboradores,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $colaborador = \App\Models\Colaborador::with('user')->find($value);
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
            'extensao_desenho' => ['required', 'integer', 'min:0'],
            'extensao_projeto' => ['required', 'integer', 'min:0'],
            'postes_desenhados' => ['required', 'integer', 'min:0'],
            'postes_projetados' => ['required', 'integer', 'min:0'],
            'tipo_projeto' => ['required', Rule::enum(TipoProjetoParte::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome da parte é obrigatório.',
            'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
            'projeto_id.required' => 'O projeto é obrigatório.',
            'projeto_id.exists' => 'O projeto selecionado não existe.',
            'extensao_desenho.required' => 'A extensão de desenho é obrigatória.',
            'extensao_desenho.integer' => 'A extensão de desenho deve ser um número inteiro.',
            'extensao_desenho.min' => 'A extensão de desenho não pode ser negativa.',
            'extensao_projeto.required' => 'A extensão de projeto é obrigatória.',
            'extensao_projeto.integer' => 'A extensão de projeto deve ser um número inteiro.',
            'extensao_projeto.min' => 'A extensão de projeto não pode ser negativa.',
            'postes_desenhados.required' => 'O número de postes desenhados é obrigatório.',
            'postes_desenhados.integer' => 'O número de postes desenhados deve ser um número inteiro.',
            'postes_desenhados.min' => 'O número de postes desenhados não pode ser negativo.',
            'postes_projetados.required' => 'O número de postes projetados é obrigatório.',
            'postes_projetados.integer' => 'O número de postes projetados deve ser um número inteiro.',
            'postes_projetados.min' => 'O número de postes projetados não pode ser negativo.',
            'tipo_projeto.required' => 'O tipo de projeto é obrigatório.',
        ];
    }
}
