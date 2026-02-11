<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\Parte;
use Illuminate\Support\Facades\DB;

class CreateOrUpdateParte
{
    public function create(int $projetoId, string $nome, ?int $colaboradorId, array $dados): Parte
    {
        return DB::transaction(function () use ($projetoId, $nome, $colaboradorId, $dados) {
            if ($colaboradorId) {
                $this->validateColaboradorCanBeAssigned($colaboradorId);
            }

            return Parte::create([
                'projeto_id' => $projetoId,
                'nome' => $nome,
                'colaborador_id' => $colaboradorId,
                'extensao_desenho' => $dados['extensao_desenho'] ?? 0,
                'extensao_projeto' => $dados['extensao_projeto'] ?? 0,
                'postes_desenhados' => $dados['postes_desenhados'] ?? 0,
                'postes_projetados' => $dados['postes_projetados'] ?? 0,
            ]);
        });
    }

    public function update(Parte $parte, string $nome, ?int $colaboradorId, array $dados): Parte
    {
        return DB::transaction(function () use ($parte, $nome, $colaboradorId, $dados) {
            if ($colaboradorId) {
                $this->validateColaboradorCanBeAssigned($colaboradorId);
            }

            $parte->update([
                'nome' => $nome,
                'colaborador_id' => $colaboradorId,
                'extensao_desenho' => $dados['extensao_desenho'] ?? 0,
                'extensao_projeto' => $dados['extensao_projeto'] ?? 0,
                'postes_desenhados' => $dados['postes_desenhados'] ?? 0,
                'postes_projetados' => $dados['postes_projetados'] ?? 0,
            ]);

            return $parte->fresh();
        });
    }

    private function validateColaboradorCanBeAssigned(int $colaboradorId): void
    {
        $colaborador = Colaborador::with('user')->findOrFail($colaboradorId);

        $allowedRoles = [
            UserRole::Levantadores,
            UserRole::Orcamentistas,
            UserRole::Projetistas,
        ];

        if (! $colaborador->user || ! in_array($colaborador->user->role, $allowedRoles, true)) {
            throw new \InvalidArgumentException(
                'O colaborador deve ter perfil Levantador, Orçamentista ou Projetista.'
            );
        }
    }
}
