<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
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
                'exists:colaboradores,id',
                function ($attribute, $value, $fail) {
                    $colaborador = \App\Models\Colaborador::with('user')->find($value);
                    if ($colaborador && $colaborador->user?->role !== UserRole::Coordenadores) {
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
