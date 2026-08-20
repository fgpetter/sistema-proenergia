<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\Projeto;
use Illuminate\Support\Facades\DB;

class CreateOrUpdateProjeto
{
    public function create(string $nome, int $colaboradorResponsavelId): Projeto
    {
        return DB::transaction(function () use ($nome, $colaboradorResponsavelId) {
            $this->validateColaboradorIsCoordenador($colaboradorResponsavelId);

            return Projeto::create([
                'nome' => $nome,
                'colaborador_responsavel_id' => $colaboradorResponsavelId,
            ]);
        });
    }

    public function update(Projeto $projeto, string $nome, int $colaboradorResponsavelId): Projeto
    {
        return DB::transaction(function () use ($projeto, $nome, $colaboradorResponsavelId) {
            $this->validateColaboradorIsCoordenador(
                $colaboradorResponsavelId,
                $projeto->colaborador_responsavel_id,
            );

            $projeto->update([
                'nome' => $nome,
                'colaborador_responsavel_id' => $colaboradorResponsavelId,
            ]);

            return $projeto->fresh();
        });
    }

    private function validateColaboradorIsCoordenador(int $colaboradorId, ?int $atribuicaoAtualId = null): void
    {
        $colaborador = Colaborador::withTrashed()
            ->with(['user' => fn ($query) => $query->withTrashed()])
            ->findOrFail($colaboradorId);

        if ($colaborador->trashed()) {
            if ($atribuicaoAtualId === null || (int) $atribuicaoAtualId !== $colaboradorId) {
                throw new \InvalidArgumentException(
                    'O colaborador selecionado não está disponível.'
                );
            }

            return;
        }

        if ($colaborador->user?->role !== UserRole::Coordenadores) {
            throw new \InvalidArgumentException(
                'O colaborador responsável deve ter perfil Coordenador.'
            );
        }
    }
}
